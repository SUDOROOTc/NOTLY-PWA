// insert_products.js

// Fonction pour ouvrir la base IndexedDB
function openDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open("NotlyDB", 2);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;

      // Création du store "produits" si inexistant
      if (!db.objectStoreNames.contains("produits")) {
        const store = db.createObjectStore("produits", { keyPath: "id", autoIncrement: true });
        store.createIndex("nom", "nom", { unique: false });
        store.createIndex("categorieId", "categorieId", { unique: false });
        store.createIndex("description", "description", { unique: false });
      }

      // Création du store "categories" si inexistant
      if (!db.objectStoreNames.contains("categories")) {
        const catStore = db.createObjectStore("categories", { keyPath: "id", autoIncrement: true });
        catStore.createIndex("nom", "nom", { unique: true });
      }
    };

    request.onsuccess = (event) => resolve(event.target.result);
    request.onerror = (event) => reject("Erreur ouverture DB : " + event.target.errorCode);
  });
}

// Liste des catégories
const categories = [
  { id: 1, nom: "Lot N°1 : Animaux pour embauche et aliments bétails + volaille" },
  { id: 2, nom: "Lot N°2 : KIT NFI" },
  { id: 3, nom: "Lot N°3 : Kits couture" },
  { id: 4, nom: "Lot N°4 : Menuiserie" },
  { id: 5, nom: "Lot N°5 : Saponification" },
  { id: 6, nom: "Lot N°6 : Kits produit alimentaire (FOOD)" },
  { id: 7, nom: "Lot N°7 : Équipement Maraîchage" },
  { id: 8, nom: "Lot N°8 : Semences" },
  { id: 9, nom: "Lot N°9 : Kits Soudure" },
  { id: 10, nom: "Lot N°10 : Kits Télécom" },
  { id: 11, nom: "Lot N°11 : Mécanique" },
  { id: 12, nom: "Lot N°12 : Friperie" },
];

// Liste des produits (exemples Lot 1 à 12, tu peux compléter avec tous tes produits)
const products = [
  // Lot 1
  { nom: "Bouc (race locale 12-18 mois)", categorieId: 1, description: "Race locale âgé entre 12 et 18 mois" },
  { nom: "Bouc (race sahel 12-18 mois)", categorieId: 1, description: "Race sahel âgé entre 12 et 18 mois" },
  { nom: "Chèvre (race locale 12-18 mois)", categorieId: 1, description: "Race locale âgé entre 12 et 18 mois" },
  { nom: "Chèvre (race sahel 12-18 mois)", categorieId: 1, description: "Race sahel âgé entre 12 et 18 mois" },
  { nom: "Bélier (race locale 12-18 mois)", categorieId: 1, description: "Race locale âgé entre 12 et 18 mois" },
  { nom: "Bélier (race métissé 12-18 mois)", categorieId: 1, description: "Race métissé âgé entre 12 et 18 mois" },
  { nom: "Brébis (race locale 12-18 mois)", categorieId: 1, description: "Race locale âgé entre 12 et 18 mois" },
  { nom: "Brébis (race métissé 12-18 mois)", categorieId: 1, description: "Race métissé âgé entre 12 et 18 mois" },
  { nom: "Corde d'attache pour animaux", categorieId: 1, description: "2 mètres pour animal, environ 10mm de diamètre" },
  { nom: "Abreuvoir (bidon 25L)", categorieId: 1, description: "En plastique" },
  { nom: "Mangeoire (bidon 25L)", categorieId: 1, description: "En plastique" },
  { nom: "Vaccination et déparasitage", categorieId: 1, description: "Par tête" },
  { nom: "Son de farine de blé (sac 50 kg)", categorieId: 1, description: "Sac de 50 kg" },
  { nom: "Tourteau en vrac (sac 50 kg)", categorieId: 1, description: "Sac de 50 kg" },
  { nom: "Pierre à lécher (1kg)", categorieId: 1, description: "Pierre salée de 1 kg" },
  { nom: "Arachides coques (sac 100 kg)", categorieId: 1, description: "Sac de 100 kg" },

  // Lot 2 (exemple résumé, tu complèteras avec tous les produits)
  { nom: "Marmite traditionnelle taille n°20 avec couvercle", categorieId: 2, description: "Traditionnelle" },
  { nom: "Marmite traditionnelle taille n°15 avec couvercle", categorieId: 2, description: "Traditionnelle" },
  // … et ainsi de suite pour Lot 2 → 12
];

// Fonction pour insérer toutes les catégories
async function insertCategories(db) {
  const tx = db.transaction("categories", "readwrite");
  const store = tx.objectStore("categories");
  categories.forEach(cat => store.add(cat));

  return new Promise((resolve, reject) => {
    tx.oncomplete = () => resolve(console.log("✅ Toutes les catégories ont été insérées"));
    tx.onerror = (e) => reject(console.error("❌ Erreur insertion catégories :", e.target.error));
  });
}

// Fonction pour insérer tous les produits
async function insertProducts(db) {
  const tx = db.transaction("produits", "readwrite");
  const store = tx.objectStore("produits");
  products.forEach(prod => store.add(prod));

  return new Promise((resolve, reject) => {
    tx.oncomplete = () => resolve(console.log("✅ Tous les produits ont été insérés"));
    tx.onerror = (e) => reject(console.error("❌ Erreur insertion produits :", e.target.error));
  });
}

// Exécution
async function main() {
  try {
    const db = await openDatabase();
    await insertCategories(db);
    await insertProducts(db);
    console.log("🎉 IndexedDB initialisée avec toutes les données !");
  } catch (err) {
    console.error(err);
  }
}

// Lancer l’insertion
main();
// Fin insert_products.js