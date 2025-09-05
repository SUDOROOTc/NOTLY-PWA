<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notly - Mode Offline</title>
<link rel="stylesheet" href="../css/acceuil.css">
</head>
<body>

<header>
    <div class="logo">Notly</div>
</header>

<div class="main-container">
    <!-- Sidebar gauche -->
    <div class="sidebar">
        <!-- Ici tu peux afficher les notes enregistrées localement -->
        <h3>Notes locales</h3>
        <ul id="notesList"></ul>
    </div>

    <!-- Contenu central -->
    <div class="content">
        <h2>Ajouter une note rapidement (Offline)</h2>

        <form id="noteForm">
            <!-- 1. Catégorie -->
            <label for="categorie">Catégorie</label>
            <select id="categorie" name="categorie" required>
                <option value="">Sélectionnez une catégorie</option>
                <option value="Animaux">Animaux</option>
                <option value="Aliments">Aliments</option>
                <option value="Outils">Outils</option>
            </select>

            <!-- 2. Produit -->
            <label for="produit">Produit</label>
            <select id="produit" name="produit" required>
                <option value="">Sélectionnez un produit</option>
            </select>

            <!-- 3. Numéro du fournisseur -->
            <label for="fournisseur">Numéro du fournisseur</label>
            <input type="text" id="fournisseur" name="fournisseur" placeholder="Numéro ou nom du fournisseur" required>

            <!-- 4. Prix -->
            <label for="prix">Prix</label>
            <input type="number" id="prix" name="prix" placeholder="Prix en XOF" required>

            <button type="submit">Ajouter la note</button>
        </form>
    </div>
</div>

<script>
let db;

// Initialisation de IndexedDB
const request = indexedDB.open('NotlyDB', 1);

request.onerror = () => console.error('Erreur lors de l’ouverture de la base IndexedDB');
request.onsuccess = () => {
    db = request.result;
    displayNotes();
};
request.onupgradeneeded = (event) => {
    db = event.target.result;
    if (!db.objectStoreNames.contains('notes')) {
        const store = db.createObjectStore('notes', { keyPath: 'id', autoIncrement: true });
        store.createIndex('categorie', 'categorie', { unique: false });
        store.createIndex('produit', 'produit', { unique: false });
    }
};

// Gestion de la liste des produits selon la catégorie
const produitsParCategorie = {
    Animaux: ['Bouc', 'Chèvre', 'Bélier'],
    Aliments: ['Son de farine', 'Tourteau', 'Pierre à lécher'],
    Outils: ['Machette', 'Couteau', 'Foyer']
};

document.getElementById('categorie').addEventListener('change', function() {
    const cat = this.value;
    const prodSelect = document.getElementById('produit');
    prodSelect.innerHTML = '<option value="">Sélectionnez un produit</option>';
    if (produitsParCategorie[cat]) {
        produitsParCategorie[cat].forEach(p => {
            const option = document.createElement('option');
            option.value = p;
            option.textContent = p;
            prodSelect.appendChild(option);
        });
    }
});

// Gestion de la soumission du formulaire
document.getElementById('noteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const categorie = document.getElementById('categorie').value;
    const produit = document.getElementById('produit').value;
    const fournisseur = document.getElementById('fournisseur').value;
    const prix = parseFloat(document.getElementById('prix').value);

    const transaction = db.transaction(['notes'], 'readwrite');
    const store = transaction.objectStore('notes');
    store.add({ categorie, produit, fournisseur, prix });

    transaction.oncomplete = () => {
        this.reset();
        displayNotes();
    };
});

// Affichage des notes locales
function displayNotes() {
    const notesList = document.getElementById('notesList');
    notesList.innerHTML = '';
    const transaction = db.transaction(['notes'], 'readonly');
    const store = transaction.objectStore('notes');
    const requestAll = store.getAll();
    requestAll.onsuccess = () => {
        requestAll.result.forEach(note => {
            const li = document.createElement('li');
            li.textContent = `${note.categorie} - ${note.produit} - ${note.fournisseur} - ${note.prix} XOF`;
            notesList.appendChild(li);
        });
    };
}
</script>

</body>
</html>
