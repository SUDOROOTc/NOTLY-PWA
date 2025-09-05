const request = indexedDB.open('NotlyDB', 1);

request.onupgradeneeded = function(event) {
  const db = event.target.result;

  // categories
  if (!db.objectStoreNames.contains('categories')) {
    const categoryStore = db.createObjectStore('categories', { keyPath: 'id', autoIncrement: true });
    categoryStore.createIndex('nom', 'nom', { unique: true });
  }

  // produits
  if (!db.objectStoreNames.contains('produits')) {
    const productStore = db.createObjectStore('produits', { keyPath: 'id', autoIncrement: true });
    productStore.createIndex('nom', 'nom', { unique: false });
    productStore.createIndex('categorieId', 'categorieId', { unique: false });
  }

  // notes
  if (!db.objectStoreNames.contains('notes')) {
    const notesStore = db.createObjectStore('notes', { keyPath: 'id', autoIncrement: true });
    notesStore.createIndex('produitId', 'produitId', { unique: false });
    notesStore.createIndex('fournisseur', 'fournisseur', { unique: false });
    notesStore.createIndex('prix', 'prix', { unique: false });
    notesStore.createIndex('dateCreation', 'dateCreation', { unique: false });
  }

  console.log("IndexedDB 'NotlyDB' et stores créés !");
};

request.onsuccess = function(event) {
  const db = event.target.result;
  console.log("Connexion à la base NotlyDB réussie !");
};

request.onerror = function(event) {
  console.error("Erreur lors de l'ouverture de la base :", event.target.error);
};
