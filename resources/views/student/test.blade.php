<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-1">{{ $category->name }} Test</h2>
        <p class="text-muted mb-0">Answer all questions. Timer is running!</p>
    </x-slot>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card mb-4">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <span><i class="fa-regular fa-clock me-2"></i>Time Left: <span id="timer">00:{{ str_pad((string)($minutes ?? 30),2,'0',STR_PAD_LEFT) }}:00</span></span>
                        <span>Question <span id="currentQ">1</span> of {{ count($questions) }}</span>
                    </div>
                </div>
                <form id="testForm" method="POST" action="{{ route('student.test.submit', ['id' => $test_id]) }}">
                    @csrf
                    <input type="hidden" name="category_id" value="{{ $category->id }}">
                    <input type="hidden" name="test_id" value="{{ $test_id ?? '' }}">
                    <input type="hidden" name="time_taken" id="time_taken" value="0">
                    <div id="questionArea">
                        @foreach ($questions as $idx => $q)
                            <div class="question-block" data-q="{{ $idx+1 }}" style="display: {{ $idx === 0 ? 'block' : 'none' }};">
                                <div class="mb-3">
                                    <span class="fw-semibold">Q{{ $idx+1 }}. {{ $q->question }}</span>
                                </div>
                                <div class="mb-3">
                                    @foreach (json_decode($q->options) as $optIdx => $opt)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="answers[{{ $q->id }}]" id="q{{ $q->id }}_{{ $optIdx }}" value="{{ $optIdx }}">
                                            <label class="form-check-label" for="q{{ $q->id }}_{{ $optIdx }}">{{ $opt }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="prevBtn" disabled>Previous</button>
                        <div>
                            @for ($i = 1; $i <= count($questions); $i++)
                                <button type="button" class="btn btn-sm btn-outline-primary jumpBtn" data-jump="{{ $i }}">{{ $i }}</button>
                            @endfor
                        </div>
                        <button type="button" class="btn btn-outline-secondary" id="nextBtn">Next</button>
                        <button type="submit" class="btn btn-success ms-2" id="submitBtn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <script>
        let currentQ = 1;
        const totalQ = {{ count($questions) }};
        function showQ(idx) {
            document.querySelectorAll('.question-block').forEach((el, i) => {
                el.style.display = (i+1) === idx ? 'block' : 'none';
            });
            document.getElementById('currentQ').innerText = idx;
            document.getElementById('prevBtn').disabled = idx === 1;
            document.getElementById('nextBtn').disabled = idx === totalQ;
        }
        document.getElementById('prevBtn').onclick = function() { if(currentQ>1) showQ(--currentQ); };
        document.getElementById('nextBtn').onclick = function() { if(currentQ<totalQ) showQ(++currentQ); };
        document.querySelectorAll('.jumpBtn').forEach(btn => {
            btn.onclick = function() { currentQ = parseInt(this.dataset.jump); showQ(currentQ); };
        });
        // Timer from settings (minutes)
        let seconds = ({{ (int)($minutes ?? 30) }}) * 60;
        let elapsed = 0;
        function updateTimer() {
            let m = Math.floor(seconds/60), s = seconds%60;
            document.getElementById('timer').innerText = `${String(Math.floor(m/60)).padStart(2,'0')}:${String(m%60).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            if(seconds>0) { seconds--; elapsed++; document.getElementById('time_taken').value = elapsed; setTimeout(updateTimer, 1000); }
            else { document.getElementById('time_taken').value = elapsed; document.getElementById('testForm').submit(); }
        }
        updateTimer();
    </script>
</x-app-layout>
