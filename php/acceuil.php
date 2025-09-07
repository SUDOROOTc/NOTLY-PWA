<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notly - Mode Offline</title>
<link rel="stylesheet" href="../css/acceuil.css">
<style>
#notesTable { width: 100%; border-collapse: collapse; margin-top: 10px; }
#notesTable th, #notesTable td { border: 1px solid #ccc; padding: 8px; text-align: left; }
#notesTable th { background-color: #f2f2f2; }
#notesTable tr:nth-child(even) { background-color: #fafafa; }
</style>
</head>
<body>

<header>
    <div class="logo">Notly</div>
</header>

<div class="main-container">
    <div class="sidebar">
        <h3>Notes</h3>
        <table id="notesTable">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Produit</th>
                    <th>Fournisseur</th>
                    <th>Prix (XOF)</th>
                </tr>
            </thead>
            <tbody id="notesList"></tbody>
        </table>
    </div>

    <div class="content">
        <h2>Ajouter une note rapidement</h2>
        <form id="noteForm">
            <label for="categorie">Catégorie</label>
            <select id="categorie" name="categorie" required>
                <option value="">Sélectionnez une catégorie</option>
            </select>

            <label for="produit">Produit</label>
            <select id="produit" name="produit" required>
                <option value="">Sélectionnez un produit</option>
            </select>

            <label for="fournisseur">Numéro du fournisseur</label>
            <input type="text" id="fournisseur" name="fournisseur" placeholder="Numéro ou nom du fournisseur" required>

            <label for="prix">Prix</label>
            <input type="number" id="prix" name="prix" placeholder="Prix en XOF" required>

            <button type="submit">Ajouter la note</button>
        </form>
    </div>
</div>

<script>
let db;

// --- Ouverture IndexedDB ---
const request = indexedDB.open('NotlyDB', 2);

request.onerror = (e) => console.error('Erreur IndexedDB', e);

request.onupgradeneeded = (e) => {
    db = e.target.result;
    if (!db.objectStoreNames.contains('notes')) {
        const store = db.createObjectStore('notes', { keyPath: 'id', autoIncrement: true });
        store.createIndex('categorieId', 'categorieId', { unique: false });
        store.createIndex('produitId', 'produitId', { unique: false });
        store.createIndex('fournisseur', 'fournisseur', { unique: false });
        store.createIndex('prix', 'prix', { unique: false });
    }
};

request.onsuccess = (e) => {
    db = e.target.result;
    console.log("Connexion IndexedDB réussie !");

    // --- Fonctions principales après que DB soit prête ---
    chargerCategories();
    displayNotes();
    syncProduitIdsWithMySQL();
    sendNotesToServerFormData();
};

// --- Charger les catégories ---
function chargerCategories() {
    const selectCategorie = document.getElementById('categorie');
    selectCategorie.innerHTML = '<option value="">Sélectionnez une catégorie</option>';
    if (!db.objectStoreNames.contains('categories')) return;

    const tx = db.transaction(['categories'], 'readonly');
    const store = tx.objectStore('categories');
    const req = store.getAll();
    req.onsuccess = () => {
        req.result.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.nom;
            selectCategorie.appendChild(option);
        });
    };
}

// --- Changer la liste des produits selon catégorie ---
document.getElementById('categorie').addEventListener('change', function() {
    const categorieId = parseInt(this.value);
    const selectProduit = document.getElementById('produit');
    selectProduit.innerHTML = '<option value="">Sélectionnez un produit</option>';
    if (!categorieId || !db.objectStoreNames.contains('produits')) return;

    const tx = db.transaction(['produits'], 'readonly');
    const store = tx.objectStore('produits');
    const index = store.index('categorieId');
    const range = IDBKeyRange.only(categorieId);
    const req = index.openCursor(range);
    req.onsuccess = (event) => {
        const cursor = event.target.result;
        if (cursor) {
            const option = document.createElement('option');
            option.value = cursor.value.id;
            option.textContent = cursor.value.nom;
            selectProduit.appendChild(option);
            cursor.continue();
        }
    };
});

// --- Ajouter une note ---
document.getElementById('noteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!db.objectStoreNames.contains('notes')) return;

    const categorieId = parseInt(document.getElementById('categorie').value);
    const produitId = parseInt(document.getElementById('produit').value);
    const fournisseur = document.getElementById('fournisseur').value;
    const prix = parseFloat(document.getElementById('prix').value);

    const tx = db.transaction(['notes'], 'readwrite');
    const store = tx.objectStore('notes');
    store.add({ categorieId, produitId, fournisseur, prix, dateCreation: new Date().toISOString() });

    tx.oncomplete = () => {
        this.reset();
        displayNotes();
    };
});

// --- Afficher les notes ---
function displayNotes() {
    const notesList = document.getElementById('notesList');
    notesList.innerHTML = '';
    if (!db.objectStoreNames.contains('notes')) return;

    const tx = db.transaction(['notes'], 'readonly');
    const store = tx.objectStore('notes');
    const req = store.getAll();
    req.onsuccess = async () => {
        const notes = req.result;
        for (const note of notes) {
            const catNom = await getCategorieNom(note.categorieId);
            const prodNom = await getProduitNom(note.produitId);
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${catNom}</td><td>${prodNom}</td><td>${note.fournisseur}</td><td>${note.prix}</td>`;
            notesList.appendChild(tr);
        }
    };
}

function getCategorieNom(id) {
    return new Promise(resolve => {
        if (!db.objectStoreNames.contains('categories')) return resolve("Inconnu");
        const tx = db.transaction(['categories'], 'readonly');
        const store = tx.objectStore('categories');
        const req = store.get(id);
        req.onsuccess = () => resolve(req.result ? req.result.nom : "Inconnu");
    });
}

function getProduitNom(id) {
    return new Promise(resolve => {
        if (!db.objectStoreNames.contains('produits')) return resolve("Inconnu");
        const tx = db.transaction(['produits'], 'readonly');
        const store = tx.objectStore('produits');
        const req = store.get(id);
        req.onsuccess = () => resolve(req.result ? req.result.nom : "Inconnu");
    });
}

// --- Synchroniser les IDs produits avec MySQL ---
function syncProduitIdsWithMySQL() {
    if (!db || !db.objectStoreNames.contains('notes')) return;

    const tx = db.transaction(['notes'], 'readwrite');
    const store = tx.objectStore('notes');
    const req = store.getAll();
    req.onsuccess = () => {
        const notes = req.result;

        // Map des produits réels MySQL
        const produitMap = {
            "Bouc (race locale 12-18 mois)": 1,
            "Bouc (race sahel 12-18 mois)": 2,
            "Chèvre (race locale 12-18 mois)": 3,
            "Chèvre (race sahel 12-18 mois)": 4,
            "Bélier (race locale 12-18 mois)": 5,
            "Bélier (race metissé 12-18 mois)": 6,
            "Brébis (race locale 12-18 mois)": 7,
            "Brébis (race metissé 12-18 mois)": 8,
            "Corde d'attache pour animaux": 9,
            "Abreuvoir (bidon 25L)": 10,
            "Mangeoire (bidon 25L)": 11,
            "Vaccination et déparasitage": 12,
            "Son de farine de blé (sac 50 kg)": 13,
            "Tourteau en vrac (sac 50 kg)": 14,
            "Pierre à lécher (1kg)": 15,
            "Arachides coques (sac 100 kg)": 16,
            "Kit hygiène de base": 17,
            "Couvertures": 18,
            "Lampes solaires": 19,
            "Bidons eau potable": 20,
            "Réchauds portables": 21,
            "Kit couture complet": 22,
            "Aiguilles et fils": 23,
            "Ciseaux professionnels": 24,
            "Tissus divers": 25
        };

        notes.forEach(note => {
            const nouveauProduitId = produitMap[note.produitNom || note.produit] || note.produitId;
            note.produitId = nouveauProduitId;
            store.put(note);
        });

        console.log("✅ Synchronisation des produitId terminée !");
    };
}

// --- Envoyer les notes au serveur ---
async function sendNotesToServerFormData() {
    console.log("🔄 Début de l'envoi des notes via FormData...");

    const tx = db.transaction("notes", "readonly");
    const store = tx.objectStore("notes");
    const req = store.getAll();

    req.onsuccess = async () => {
        const notesArray = req.result;
        if (!notesArray || notesArray.length === 0) {
            console.warn("⚠️ Aucune note à envoyer.");
            return;
        }

        for (const note of notesArray) {
            const formData = new FormData();
            formData.append('categorieId', note.categorieId);
            formData.append('produitId', note.produitId);
            formData.append('fournisseur', note.fournisseur);
            formData.append('prix', note.prix);
            formData.append('dateCreation', note.dateCreation);

            try {
                const response = await fetch('sync_notes.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.text();
                console.log("Réponse serveur pour cette note :", result);
            } catch (error) {
                console.error("❌ Erreur lors de l'envoi de la note :", error);
            }
        }
    };
}
</script>

</body>
</html>

