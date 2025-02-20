<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Sondage</title>
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
        <h1 class="text-center fw-bold">Créer un Sondage</h1>

        <!-- Affichage des messages de succès -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Formulaire de création du sondage -->
        <div class="card shadow-sm mt-4 bg-light">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Détails du Sondage</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('sondages.store') }}" method="POST">
                    @csrf

                    <!-- Informations du sondage -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Titre du sondage</label>
                        <input type="text" name="titre" class="form-control" value="{{ old('titre') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date de début</label>
                            <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date de fin</label>
                            <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin') }}" required>
                        </div>
                    </div>

                    <!-- Questions -->
                    <div id="questions-container" class="mt-4">
                        <h4>Questions</h4>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addQuestion()">
                            + Ajouter une question
                        </button>
                    </div>

                    <button type="submit" class="btn btn-success mt-4 w-100">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let questionIndex = 0;

        function addQuestion() {
            questionIndex++;
            const container = document.getElementById('questions-container');

            const questionDiv = document.createElement('div');
            questionDiv.classList.add('border', 'p-3', 'mb-3', 'rounded', 'bg-light');
            questionDiv.innerHTML = `
                <h5 class="fw-bold">Question ${questionIndex}</h5>
                <label class="form-label fw-bold">Intitulé de la question</label>
                <input type="text" name="questions[${questionIndex - 1}][title]" class="form-control mb-2" required>

                <label class="form-label fw-bold">Question</label>
                <input type="text" name="questions[${questionIndex - 1}][question]" class="form-control mb-2" required>

                <label class="form-label fw-bold">Type de question</label>
                <select name="questions[${questionIndex - 1}][type_question]" class="form-select mb-2" onchange="handleTypeChange(this, ${questionIndex - 1})">
                    <option value="text">Réponse libre</option>
                    <option value="multiple">Choix multiples</option>
                    <option value="checkbox">Cases à cocher</option>
                </select>

                <div id="options-container-${questionIndex - 1}" class="mb-2"></div>

                <button type="button" onclick="removeQuestion(this)" class="btn btn-danger btn-sm">
                    Supprimer la question
                </button>
            `;

            container.appendChild(questionDiv);
        }

        function handleTypeChange(select, index) {
            const optionsContainer = document.getElementById(`options-container-${index}`);
            optionsContainer.innerHTML = "";

            if (select.value === "multiple" || select.value === "checkbox") {
                const addOptionButton = document.createElement('button');
                addOptionButton.type = "button";
                addOptionButton.textContent = "+ Ajouter une option";
                addOptionButton.classList.add("btn", "btn-warning", "btn-sm", "mt-2");
                addOptionButton.onclick = () => addOption(index);
                optionsContainer.appendChild(addOptionButton);
            }
        }

        function addOption(index) {
            const optionsContainer = document.getElementById(`options-container-${index}`);
            const optionDiv = document.createElement('div');
            optionDiv.innerHTML = `<input type="text" name="questions[${index}][options][]" class="form-control mb-2" required>`;
            optionsContainer.appendChild(optionDiv);
        }

        function removeQuestion(button) {
            button.parentElement.remove();
        }
    </script>

</body>
</html>
