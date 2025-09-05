// insert_lots1_2.js
(function() {
  const request = indexedDB.open("NotlyDB", 2);

  request.onsuccess = function(event) {
    const db = event.target.result;
    console.log("Connexion à NotlyDB réussie pour insertion lots 1 & 2");

    const produits = [
      // Lot 1 : Animaux pour embauche et aliments bétails + volaille
      { nom: "Bouc (race locale, agé entre 12 et 18 mois)", categorieId: 1 },
      { nom: "Bouc (race sahel, agé entre 12 et 18 mois)", categorieId: 1 },
      { nom: "Chèvre (race locale, agé entre 12 et 18 mois)", categorieId: 1 },
      { nom: "Chèvre (race sahel, agé entre 12 et 18 mois)", categorieId: 1 },
      { nom: "Bélier (race locale, agé entre 12 et 18 mois)", categorieId: 1 },
      { nom: "Bélier (race metissé, agé entre 12 et 18 mois)", categorieId: 1 },
      { nom: "Brébis (race locale, agé entre 12 et 18 mois)", categorieId: 1 },
      { nom: "Brébis (race metissé, agé entre 12 et 18 mois)", categorieId: 1 },
      { nom: "Corde d'attache pour animaux", categorieId: 1 },
      { nom: "Abreuvoir (bidon 25L)", categorieId: 1 },
      { nom: "Mangeoire (bidon 25L)", categorieId: 1 },
      { nom: "Vaccination et déparasitage", categorieId: 1 },
      { nom: "Son de farine de blé (sac 50 kg)", categorieId: 1 },
      { nom: "Tourteau en vrac (sac 50 kg)", categorieId: 1 },
      { nom: "Pierre à lécher type rectangle couleur blanche (1kg)", categorieId: 1 },
      { nom: "Arachides coques (sac 100kg)", categorieId: 1 },

      // Lot 2 : KIT NFI
      { nom: "Marmite traditionnelle taille n°20 avec couvercle", categorieId: 2 },
      { nom: "Marmite traditionnelle taille n°15 avec couvercle", categorieId: 2 },
      { nom: "Casserole en aluminium avec couvercle taille 20", categorieId: 2 },
      { nom: "Casserole en aluminium avec couvercle taille 10", categorieId: 2 },
      { nom: "Marmite traditionnelle taille n°07 avec couvercle", categorieId: 2 },
      { nom: "Marmite traditionnelle taille n°05 avec couvercle", categorieId: 2 },
      { nom: "Marmite traditionnelle taille n°30 avec couvercle", categorieId: 2 },
      { nom: "Assiette en aluminium 50cm de diamètre minimum", categorieId: 2 },
      { nom: "Assiette en fer 25cm de diamètre minimum", categorieId: 2 },
      { nom: "Passoire en aluminium lion (40cm)", categorieId: 2 },
      { nom: "Emballage plastique noir petit format", categorieId: 2 },
      { nom: "Emballage plastique noir grand format", categorieId: 2 },
      { nom: "Emballage plastique transparent petit format", categorieId: 2 },
      { nom: "Emballage plastique transparent grand format", categorieId: 2 },
      { nom: "Grande louche pleine en aluminium", categorieId: 2 },
      { nom: "Grande louche perforée en aluminium", categorieId: 2 },
      { nom: "Louche moyenne pleine en aluminium", categorieId: 2 },
      { nom: "Louche moyenne perforée en aluminium", categorieId: 2 },
      { nom: "Grande écumoire", categorieId: 2 },
      { nom: "Moyenne écumoire", categorieId: 2 },
      { nom: "Grande et moyenne poêle", categorieId: 2 },
      { nom: "Grande bassine", categorieId: 2 },
      { nom: "Petite bassine en aluminium (30L)", categorieId: 2 },
      { nom: "Bassine en aluminium de 20L", categorieId: 2 },
      { nom: "Grande bassine plastique", categorieId: 2 },
      { nom: "Seau en plastique avec couvercle (10L)", categorieId: 2 },
      { nom: "Seau en plastique avec couvercle (15L)", categorieId: 2 },
      { nom: "Seau en plastique avec couvercle (20L)", categorieId: 2 },
      { nom: "Bol en plastique moyen", categorieId: 2 },
      { nom: "Seau en plastique avec couvercle (25L)", categorieId: 2 },
      { nom: "Seau en plastique (type dari samaracolo 15L)", categorieId: 2 },
      { nom: "Seau en plastique (type dari samaracolo 20L)", categorieId: 2 },
      { nom: "Seau en plastique (type dari samaracolo 25L)", categorieId: 2 },
      { nom: "Seau en plastique (type dari samaracolo 30L)", categorieId: 2 },
      { nom: "Seau transparent avec couvercle 15L", categorieId: 2 },
      { nom: "Seau transparent avec couvercle 20L", categorieId: 2 },
      { nom: "Seau transparent avec couvercle 25L", categorieId: 2 },
      { nom: "Seau transparent avec couvercle 30L", categorieId: 2 },
      { nom: "Seau sans couvercle de 15L (industrielle aluminium)", categorieId: 2 },
      { nom: "Seau sans couvercle de 20L (industrielle aluminium)", categorieId: 2 },
      { nom: "Seau plastique avec couvercle 20L", categorieId: 2 },
      { nom: "Fût 100L avec couvercle", categorieId: 2 },
      { nom: "Baril plastique 200L avec couvercle", categorieId: 2 },
      { nom: "Bâche plastique (4x5m)", categorieId: 2 },
      { nom: "Parasole grand format", categorieId: 2 },
      { nom: "Lampe solaire rechargeable LED 3W", categorieId: 2 },
      { nom: "Balance de colis électronique 150kg", categorieId: 2 },
      { nom: "Balance mécanique 20kg", categorieId: 2 },
      { nom: "Panier en plastique perforé grand", categorieId: 2 },
      { nom: "Table en plastique 1m²", categorieId: 2 },
      { nom: "Chaise en plastique", categorieId: 2 },
      { nom: "Bidon vide 20L avec couvercle recyclé", categorieId: 2 },
      { nom: "Présentoir en plastique grand avec couvercle", categorieId: 2 },
      { nom: "Cuvette en plastique 30L", categorieId: 2 },
      { nom: "Gobelet avec manche 0,5L", categorieId: 2 },
      { nom: "Gobelet avec manche 1L", categorieId: 2 },
      { nom: "Glaciaire 20L", categorieId: 2 },
      { nom: "Glaciaire 30L", categorieId: 2 },
      { nom: "Glaciaire 40L", categorieId: 2 },
      { nom: "Glaciaire 50L", categorieId: 2 },
      { nom: "Panier traditionnel africain", categorieId: 2 },
      { nom: "Grille pour grillade 50x100cm", categorieId: 2 },
      { nom: "Savon kabakourou 250g", categorieId: 2 },
      { nom: "Savon kabakourou 300g", categorieId: 2 },
      { nom: "Savon kabakourou 400g", categorieId: 2 },
      { nom: "Savon Citec 250g ou équivalent", categorieId: 2 },
      { nom: "Savon Citec 400g ou équivalent", categorieId: 2 },
      { nom: "Omo type Saba (12 sachets 1kg)", categorieId: 2 },
      { nom: "Omo type Saba (153 sachets)", categorieId: 2 },
      { nom: "Spatule bois petit format", categorieId: 2 },
      { nom: "Spatule bois grand format", categorieId: 2 },
      { nom: "Bouteille de gaz 6kg SODIGAZ", categorieId: 2 },
      { nom: "Bouteille de gaz 12kg SODIGAZ", categorieId: 2 },
      { nom: "Bouteille de gaz 6kg type ECO", categorieId: 2 },
      { nom: "Bouteille de gaz 12kg type ECO", categorieId: 2 },
      { nom: "Écumoire métallique petit format", categorieId: 2 },
      { nom: "Écumoire métallique moyen format", categorieId: 2 },
      { nom: "Écumoire métallique grand format", categorieId: 2 },
      { nom: "Épuisette en plastique 30cm", categorieId: 2 },
      { nom: "Épuisette en plastique 50cm", categorieId: 2 },
      { nom: "Fourchette en plastique grande", categorieId: 2 },
      { nom: "Fourchette en plastique moyenne", categorieId: 2 },
      { nom: "Cuillère en plastique grande", categorieId: 2 },
      { nom: "Cuillère en plastique moyenne", categorieId: 2 },
      { nom: "Bol en plastique grand 3L", categorieId: 2 }
    ];

    const tx = db.transaction("produits", "readwrite");
    const store = tx.objectStore("produits");
    produits.forEach(p => store.add(p));

    tx.oncomplete = () => console.log("✅ Lots 1 & 2 insérés !");
    tx.onerror = e => console.error("❌ Erreur insertion lots 1 & 2 :", e.target.error);
  };

  request.onerror = function(event) {
    console.error("Erreur ouverture DB :", event.target.error);
  };
})();
