<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\Survey;
use App\Models\Transaction;
use App\Models\DashboardBanner;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Statistik survey milik user
        $totalSurveys = Survey::where('user_id', $user->id)->count();
        $totalQuestions = Survey::where('user_id', $user->id)->sum('question_count');
        $totalTargetResponden = Survey::where('user_id', $user->id)->get()->sum('respondent_count');

        $responsesQuery = Response::whereNotNull('input_by_admin_id')
            ->whereHas('survey', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

        $totalRespondenDiperoleh = (clone $responsesQuery)->sum('respond_count');

        // Performance metrics
        $activeSurveys = Survey::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $responseRate = $totalTargetResponden > 0
            ? round(($totalRespondenDiperoleh / $totalTargetResponden) * 100, 1)
            : 0;

        $completedSurveys = Survey::where('user_id', $user->id)
            ->get()
            ->filter(fn ($s) => $s->adminResponses->sum('respond_count') >= $s->respondent_count)
            ->count();

        $completionRate = $totalSurveys > 0
            ? round(($completedSurveys / $totalSurveys) * 100, 1)
            : 0;

        $avgCompletionDays = Survey::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(DATEDIFF(completed_at, created_at)) as avg_days')
            ->value('avg_days') ?? 0;

        // Statistik transaksi
        $totalSpent = Transaction::where('user_id', $user->id)->where('status', Transaction::STATUS_PAID)->sum('amount');

        // Survey terbaru milik user
        $recentSurveys = Survey::where('user_id', $user->id)
            ->withSum('adminResponses', 'respond_count')
            ->with(['transactions' => function ($query) {
                $query->latest();
            }])
            ->latest()
            ->take(5)
            ->get();

        // Dashboard banners untuk slider (gambar saja)
        $banners = DashboardBanner::where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->orderBy('order')
            ->get();

        // 7-day chart data
        $chartData = Response::whereHas('survey', fn ($q) => $q->where('user_id', $user->id))
            ->whereNotNull('input_by_admin_id')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(respond_count) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // Build 7-day labels and fill missing days with 0
        $chartLabels = [];
        $chartRespondents = [];
        $chartTargets = [];
        $dailyTarget = $totalTargetResponden > 0
            ? round($totalTargetResponden / max($totalSurveys, 1) / 7, 1)
            : 0;

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartRespondents[] = (int) ($chartData[$date] ?? 0);
            $chartTargets[] = $dailyTarget;
        }

        // Sparkline data arrays (last 7 days)
        $sparkSurveysData = Survey::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $sparkQuestionsData = Survey::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(question_count) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $sparkTargetRespondenData = Survey::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(respondent_count) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $sparkRespondenDiperolehData = Response::whereHas('survey', fn ($q) => $q->where('user_id', $user->id))
            ->whereNotNull('input_by_admin_id')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(respond_count) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $sparkTransactionsData = Transaction::where('user_id', $user->id)
            ->where('status', Transaction::STATUS_PAID)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $sparkSurveys = [];
        $sparkQuestions = [];
        $sparkTargetResponden = [];
        $sparkRespondenDiperoleh = [];
        $sparkTransactions = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $sparkSurveys[] = (int) ($sparkSurveysData[$date] ?? 0);
            $sparkQuestions[] = (int) ($sparkQuestionsData[$date] ?? 0);
            $sparkTargetResponden[] = (int) ($sparkTargetRespondenData[$date] ?? 0);
            $sparkRespondenDiperoleh[] = (int) ($sparkRespondenDiperolehData[$date] ?? 0);
            $sparkTransactions[] = (float) ($sparkTransactionsData[$date] ?? 0);
        }

        return view('user.dashboard.index', compact(
            'user',
            'totalSurveys',
            'totalQuestions',
            'totalTargetResponden',
            'totalRespondenDiperoleh',
            'totalSpent',
            'recentSurveys',
            'banners',
            'activeSurveys',
            'responseRate',
            'completionRate',
            'avgCompletionDays',
            'chartLabels',
            'chartRespondents',
            'chartTargets',
            'sparkSurveys',
            'sparkQuestions',
            'sparkTargetResponden',
            'sparkRespondenDiperoleh',
            'sparkTransactions'
        ));
    }
}
