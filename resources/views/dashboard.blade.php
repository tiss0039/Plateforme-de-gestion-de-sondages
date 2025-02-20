<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord</title>
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
        <h1 class="text-center fw-bold">Bienvenue, {{ auth()->user()->name }}</h1>

        <!-- Affichage des messages de succès -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Box des sondages -->
        <div class="card shadow-sm mt-4 bg-light">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Mes Sondages</h2>
                <a href="{{ route('sondages.create') }}" class="btn btn-success">Créer un Sondage</a>
            </div>
            <div class="card-body">
                @if ($sondages->isEmpty())
                    <p class="text-center text-muted">Aucun sondage trouvé.</p>
                @else
                    <table class="table table-hover table-light text-center align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Statut</th>
                                <th>Date Début</th>
                                <th>Date Fin</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sondages as $sondage)
                                <tr>
                                    <td class="fw-bold">{{ $sondage->id }}</td>
                                    <td>{{ $sondage->titre }}</td>
                                    <td>{{ Str::limit($sondage->description, 50) }}</td>
                                    <td>{{ $sondage->statut }}</td>
                                    <td>{{ \Carbon\Carbon::parse($sondage->date_debut)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($sondage->date_fin)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('sondages.show', $sondage) }}" class="btn btn-primary btn-sm">Voir</a>
                                        <a href="{{ route('sondages.edit', $sondage) }}" class="btn btn-warning btn-sm">Modifier</a>
                                        <form action="{{ route('sondages.destroy', $sondage) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



