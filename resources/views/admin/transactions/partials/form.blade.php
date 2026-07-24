<div>
    <label class="block mb-1 font-medium">User</label>
    <select id="userSelect" name="user_id" class="w-full border rounded px-3 py-2">
        <option value="">-- Select User (optional) --</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}"
                {{ old('user_id', optional($transaction)->user_id) == $user->id ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
        @endforeach
    </select>
</div>

<div>
    <label class="block mb-1 font-medium">Survey</label>

    @if (isset($transaction))
        <!-- Edit form: dropdown survey -->
        <select id="surveySelect" name="survey_id" class="w-full border rounded px-3 py-2"
            data-selected="{{ $transaction->survey_id }}">
            <option value="">-- Select Survey --</option>
            @foreach ($surveys as $survey)
                <option value="{{ $survey->id }}" data-user="{{ $survey->user_id }}"
                    {{ $transaction->survey_id == $survey->id ? 'selected' : '' }}>
                    {{ $survey->title }}
                </option>
            @endforeach
        </select>
    @else
        <!-- Create form: input teks biasa -->
        <input type="text" name="survey_title" id="surveyInput" placeholder="Masukkan nama survey"
            class="w-full border rounded px-3 py-2 text-gray-700">

        <!-- Hidden input untuk survey_id -->
        <input type="hidden" name="survey_id" id="surveyIdInput" value="">
        <p class="text-gray-500 text-sm italic mt-1">Survey akan otomatis terhubung jika user sudah dipilih.</p>
    @endif
</div>

<div>
    <label class="block mb-1 font-medium">Jumlah Pertanyaan</label>
    @if (isset($transaction) && $transaction->survey)
        <input type="number" name="question_count" value="{{ $transaction->survey->question_count }}"
            class="w-full border rounded px-3 py-2" readonly>
    @else
        <input type="number" name="question_count" value="{{ old('question_count', 0) }}"
            placeholder="Masukkan jumlah pertanyaan" min="0"
            class="w-full border rounded px-3 py-2">
    @endif
</div>


<div>
    <label class="block mb-1 font-medium">Amount</label>
    <input type="number" name="amount" value="{{ old('amount', optional($transaction)->amount) }}"
        class="w-full border rounded px-3 py-2" required>
</div>

<div>
    <label class="block mb-1 font-medium">Status</label>
    <select name="status" class="w-full border rounded px-3 py-2" required>
        @foreach (['pending', 'paid', 'failed', 'refunded'] as $status)
            <option value="{{ $status }}"
                {{ old('status', optional($transaction)->status) == $status ? 'selected' : '' }}>
                {{ ucfirst($status) }}
            </option>
        @endforeach
    </select>
</div>

<div>
    <label class="block mb-1 font-medium">Payment Method</label>
    <select name="payment_method" class="w-full border rounded px-3 py-2">
        @foreach (['bank_transfer', 'Qriss'] as $method)
            <option value="{{ $method }}"
                {{ old('payment_method', optional($transaction)->payment_method) == $method ? 'selected' : '' }}>
                {{ ucfirst(str_replace('_', ' ', $method)) }}
            </option>
        @endforeach
    </select>
</div>



<div>
    <label class="block mb-1 font-medium">SingaPay Ref</label>
    <input type="text" name="singapay_ref" value="{{ old('singapay_ref', optional($transaction)->singapay_ref) }}"
        class="w-full border rounded px-3 py-2">
</div>

<script>
    $(document).ready(function() {
        $('#userSelect, #surveySelect').select2();

        function filterSurvey() {
            const userId = $('#userSelect').val();
            const selectedSurvey = $('#surveySelect').data('selected'); // survey yang sedang dipilih

            $('#surveySelect option').each(function() {
                const surveyUser = $(this).data('user');
                const value = $(this).val();

                if (value === "" || value == selectedSurvey) {
                    $(this).show();
                } else if (userId && surveyUser != userId) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });

            // jika edit form, tetap pilih survey yang sudah ada
            $('#surveySelect').val(selectedSurvey).trigger('change');
        }

        $('#userSelect').on('change', filterSurvey);

        // trigger filter saat load edit form
        filterSurvey();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userSelect = document.getElementById('userSelect'); // select user
        const surveyInput = document.getElementById('surveyInput'); // input teks survey
        const surveyIdInput = document.getElementById('surveyIdInput'); // hidden survey_id

        const surveys = @json($surveys); // data surveys dari backend

        if (userSelect) {
            userSelect.addEventListener('change', function() {
                const userId = parseInt(this.value);
                const userSurvey = surveys.find(s => s.user_id === userId);

                if (userSurvey) {
                    // Jika user sudah punya survey, otomatis isi survey_id dan judul
                    surveyIdInput.value = userSurvey.id;
                    surveyInput.value = userSurvey.title;
                    surveyInput.disabled = true; // nonaktifkan input karena sudah ada
                } else {
                    // Jika user belum punya survey, kosongkan input
                    surveyIdInput.value = '';
                    surveyInput.value = '';
                    surveyInput.disabled = false; // aktifkan untuk input baru
                }
            });
        }
    });
</script>
