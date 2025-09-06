<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">Choose Test Category</h2>
    </x-slot>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <div class="row g-3">
                    @foreach ($categories as $cat)
                    <div class="col-12 col-md-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title mb-2">{{ $cat->name }}</h5>
                                    <p class="text-muted mb-0">Start a {{ $cat->name }} test</p>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('student.test', ['id' => $cat->id]) }}" class="btn btn-primary">Start</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <style>
        .hero-dashboard { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 36px 0; position: relative; overflow: hidden; }
        .hero-dashboard .hero-icon { position: absolute; right: -40px; bottom: -20px; font-size: 10rem; opacity: 0.12; }
        .stat-card { border: 0; box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-radius: 14px; }
    </style>

    <section class="hero-dashboard">
        <div class="container d-flex align-items-center justify-content-between">
            <div>
                <h1 class="display-6 fw-bold mb-1">Choose a Category</h1>
                <p class="mb-0 opacity-75">Select a test category to begin.</p>
            </div>
            <i class="fa-solid fa-layer-group hero-icon"></i>
        </div>
    </section>

    <div class="container py-4">
        <div class="row justify-content-center g-4">
            @foreach ($categories as $cat)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card stat-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fa-solid {{ $cat->name === 'Aptitude' ? 'fa-brain text-danger' : 'fa-microchip text-primary' }} fa-3x mb-3"></i>
                            <h5 class="card-title mb-2">{{ $cat->name }}</h5>
                            <p class="text-muted">{{ $cat->name === 'Aptitude' ? 'Quant, reasoning, and verbal.' : 'Coding, DBMS, and CS basics.' }}</p>
                            <a href="{{ route('student.test', $cat->id) }}" class="btn btn-primary btn-lg w-100">Start Test</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</x-app-layout>
