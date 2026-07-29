<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Models\Transaction;
use App\Models\FollowUp;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;

class CRMController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get follow-ups data
        $followUps = FollowUp::with('customer')
            ->latest('follow_up_date')
            ->take(5)
            ->get();

        // Get customers with paid transactions
        $customerAlready = User::whereHas('transactions', function ($query) {
            $query->where('status', Transaction::STATUS_PAID);
        })->with(['transactions' => function ($query) {
            $query->where('status', Transaction::STATUS_PAID);
            $query->with([
                'survey' => function ($surveyQuery) {
                    $surveyQuery->select('id', 'title', 'form_link')
                        ->with(['responses' => function ($responseQuery) {
                            $responseQuery->select('id', 'survey_id', 'google_form_link', 'input_by_admin_id', 'updated_at')
                                ->whereNull('input_by_admin_id')
                                ->latest('updated_at');
                        }]);
                }
            ]);
        }])->latest()->get();

        // Pipeline data from customers table
        $pipeline = [
            'lead'      => Customer::where('status', 'lead')->count(),
            'prospect'  => Customer::where('status', 'prospect')->count(),
            'customer'  => Customer::where('status', 'customer')->count(),
        ];

        // Transaction status counts
        $transactionStats = [
            'pending' => Transaction::where('status', Transaction::STATUS_PENDING)->count(),
            'paid'    => Transaction::where('status', Transaction::STATUS_PAID)->count(),
        ];

        // Monthly revenue for the last 6 months
        $monthlyRevenue = [];
        $monthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabels[] = $date->translatedFormat('M Y');
            $monthlyRevenue[] = Transaction::where('status', Transaction::STATUS_PAID)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
        }

        // Follow-up status counts
        $followUpStats = [
            'pending'     => FollowUp::where('status', 'pending')->count(),
            'contacted'   => FollowUp::where('status', 'contacted')->count(),
            'negotiation' => FollowUp::where('status', 'negotiation')->count(),
            'closed'      => FollowUp::where('status', 'closed')->count(),
        ];

        $stats = [
            ['title' => 'CUSTOMER SUDAH BAYAR', 'value' => $customerAlready->count()],
            ['title' => 'TOTAL PEMBAYARAN', 'value' => 'Rp ' . number_format($customerAlready->sum(function($user) { return $user->transactions->sum('amount'); }), 0, ',', '.')],
            ['title' => 'TRANSAKSI BERHASIL', 'value' => $customerAlready->sum(function($user) { return $user->transactions->count(); })],
            ['title' => 'TOTAL LEAD', 'value' => $pipeline['lead']],
            ['title' => 'TOTAL PROSPECT', 'value' => $pipeline['prospect']],
            ['title' => 'TOTAL CUSTOMER', 'value' => $pipeline['customer']],
        ];

        return view('admin.crm.dashboard', compact(
            'customerAlready', 'followUps', 'stats',
            'pipeline', 'transactionStats', 'monthlyRevenue', 'monthLabels', 'followUpStats'
        ));
    }

    public function clientMenu()
    {
        try {
            // Get follow-ups data
            $followUps = FollowUp::with('customer')
                ->latest('follow_up_date')
                ->take(5)
                ->get();

            // Get users with paid transactions
            $customerAlready = User::whereHas('transactions', function ($query) {
                $query->where('status', Transaction::STATUS_PAID);
            })->with(['transactions' => function ($query) {
                $query->where('status', Transaction::STATUS_PAID);
                $query->with([
                    'survey' => function ($surveyQuery) {
                        $surveyQuery->select('id', 'title', 'form_link')
                            ->with(['responses' => function ($responseQuery) {
                                $responseQuery->select('id', 'survey_id', 'google_form_link', 'input_by_admin_id', 'updated_at')
                                    ->whereNull('input_by_admin_id')
                                    ->latest('updated_at');
                            }]);
                    }
                ]);
            }])->latest()->take(5)->get();

            return view('admin.crm', compact('followUps', 'customerAlready'));
        } catch (\Exception $e) {
            // Fallback dengan data kosong jika ada error
            return view('admin.crm', [
                'followUps' => collect(),
                'customerAlready' => collect()
            ]);
        }
    }

    public function customerAlready(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $role = $request->input('role', 'all');
        $respondenFilter = $request->input('is_responden', 'all');
        $perPage = (int) $request->input('per_page', 10);
        $allowedPerPage = [10, 25, 50];

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $query = User::with(['transactions' => function ($query) {
            $query->with([
                'survey' => function ($surveyQuery) {
                    $surveyQuery->select('id', 'title', 'form_link')
                        ->with(['responses' => function ($responseQuery) {
                            $responseQuery->select('id', 'survey_id', 'google_form_link', 'input_by_admin_id', 'updated_at')
                                ->whereNull('input_by_admin_id')
                                ->latest('updated_at');
                        }]);
                }
            ])->latest();
        }])->latest();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        if ($role === 'admin') {
            $query->where('is_admin', true);
        } elseif ($role === 'user') {
            $query->where('is_admin', false);
        }

        if ($respondenFilter === '1') {
            $query->where('is_responden', true);
        } elseif ($respondenFilter === '0') {
            $query->where('is_responden', false);
        }

        $users = $query->paginate($perPage)->withQueryString();

        return view('admin.crm.customer-already', compact('users', 'search', 'role', 'respondenFilter', 'perPage'));
    }

    /**
     * Toggle is_responden status for a user.
     */
    public function toggleResponden(User $user)
    {
        $user->update([
            'is_responden' => !$user->is_responden,
        ]);

        $status = $user->is_responden ? 'aktifkan' : 'nonaktifkan';
        return back()->with('success', "Akses responden untuk {$user->name} berhasil di-{$status}.");
    }

    public function showManageUser(User $user, Request $request)
    {
        $trxStatus = $request->input('trx_status', 'all');
        $allowedStatus = ['all', Transaction::STATUS_PENDING, Transaction::STATUS_PROCESSING, Transaction::STATUS_PAID, Transaction::STATUS_FAILED];

        if (!in_array($trxStatus, $allowedStatus, true)) {
            $trxStatus = 'all';
        }

        $transactionsQuery = Transaction::where('user_id', $user->id)
            ->with(['survey' => function ($query) {
                $query->select('id', 'title', 'form_link');
            }])
            ->latest();

        if ($trxStatus !== 'all') {
            $transactionsQuery->where('status', $trxStatus);
        }

        $transactions = $transactionsQuery->paginate(10)->withQueryString();

        $stats = [
            'total_surveys' => Survey::where('user_id', $user->id)->count(),
            'total_transactions' => Transaction::where('user_id', $user->id)->count(),
            'total_paid_amount' => Transaction::where('user_id', $user->id)
                ->where('status', Transaction::STATUS_PAID)
                ->sum('amount'),
            'total_paid_transactions' => Transaction::where('user_id', $user->id)
                ->where('status', Transaction::STATUS_PAID)
                ->count(),
        ];

        return view('admin.crm.user-detail', compact('user', 'transactions', 'stats', 'trxStatus'));
    }
}
