<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voir le Sondage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barre de navigation -->
    <nav class="navbar navbar-light bg-white shadow-sm px-4">
        <a class="navbar-brand fw-bold text-primary" href="{{ route('dashboard') }}">Tableau de Bord</a>
        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">Voir mon profil</a>
    </nav>

    <!-- Contenu principal -->
    <div class="container my-4">
        <h1 class="text-center fw-bold">Sondage : {{ $sondage->titre }}</h1>

        <!-- Affichage des messages de succès -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Détails du sondage -->
        <div class="card shadow-sm mt-4 bg-light">
            <div class="card-header">
                <h2 class="mb-0">Détails du Sondage</h2>
            </div>
            <div class="card-body">
                <h4>Description</h4>
                <p>{{ $sondage->description }}</p>

                <h4>Date de début : {{ \Carbon\Carbon::parse($sondage->start_date)->format('d/m/Y') }}</h4>
                <h4>Date de fin : {{ \Carbon\Carbon::parse($sondage->end_date)->format('d/m/Y') }}</h4>
                
                <h4 class="mt-4">Questions</h4>
                @forelse ($sondage->questions as $question)
                    <div class="card mb-3">
                        <div class="card-header">
                            Question {{ $loop->iteration }}: {{ $question->title }}
                        </div>
                        <div class="card-body">
                            <p>Type de question : {{ $question->question_type }}</p>
                            @if (in_array($question->question_type, ['multiple', 'checkbox']))
                                <h5>Options :</h5>
                                <ul>
                                    @foreach ($question->options as $option)
                                        <li>{{ $option->option }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Aucune question pour ce sondage.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

