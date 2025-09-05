const requete = indexedDB.open('NotlyDB', 2);

requete.onupgradeneeded = function(evenement) {
  const base = evenement.target.result;

  // catégories
  if (!base.objectStoreNames.contains('categories')) {
    const magasinCategories = base.createObjectStore('categories', { keyPath: 'id', autoIncrement: true });
    magasinCategories.createIndex('nom', 'nom', { unique: true });
  }

  // produits
  if (!base.objectStoreNames.contains('produits')) {
    const magasinProduits = base.createObjectStore('produits', { keyPath: 'id', autoIncrement: true });
    magasinProduits.createIndex('nom', 'nom', { unique: false });
    magasinProduits.createIndex('categorieId', 'categorieId', { unique: false });
  }

  // notes
  if (!base.objectStoreNames.contains('notes')) {
    const magasinNotes = base.createObjectStore('notes', { keyPath: 'id', autoIncrement: true });
    magasinNotes.createIndex('produitId', 'produitId', { unique: false });
    magasinNotes.createIndex('fournisseur', 'fournisseur', { unique: false });
    magasinNotes.createIndex('prix', 'prix', { unique: false });
    magasinNotes.createIndex('dateCreation', 'dateCreation', { unique: false });
  }

  console.log("IndexedDB 'NotlyDB' et magasins créés !");

  // Données initiales des catégories
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
    { id: 12, nom: "Lot N°12 : Friperie" }
  ];

  // Créer une transaction unique pour insérer toutes les catégories
  const transaction = evenement.target.transaction;
  const store = transaction.objectStore('categories');
  categories.forEach(categorie => store.put(categorie));
};

requete.onsuccess = function(evenement) {
  const base = evenement.target.result;
  console.log("Connexion à la base NotlyDB réussie !");
};

requete.onerror = function(event) { // correction : requete.onerror
  console.error("Erreur lors de l'ouverture de la base :", event.target.error);
  alert("Une erreur est survenue lors de l'accès à la base de données. Vérifiez que votre navigateur supporte IndexedDB.");
};
