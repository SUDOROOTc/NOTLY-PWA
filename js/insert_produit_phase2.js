// insert_lots3_12.js
(function() {
  const request = indexedDB.open("NotlyDB", 2);

  request.onsuccess = function(event) {
    const db = event.target.result;
    console.log("Connexion à NotlyDB réussie pour insertion lots 3 à 12");

    const produits = [
      // Lot 3 : Kits couture
      { nom: "Kit couture complet", categorieId: 3 },

      // Lot 4 : Menuiserie
      { nom: "Kit menuiserie complet", categorieId: 4 },

      // Lot 5 : Saponification
      { nom: "Kit saponification complet", categorieId: 5 },

      // Lot 6 : Kits produit alimentaire (FOOD)
      { nom: "Kit alimentaire complet", categorieId: 6 },

      // Lot 7 : Équipement Maraîchage
      { nom: "Équipement maraîchage complet", categorieId: 7 },

      // Lot 8 : Semences
      { nom: "Semences variées", categorieId: 8 },

      // Lot 9 : Kits Soudure
      { nom: "Kit soudure complet", categorieId: 9 },

      // Lot 10 : Kits Télécom
      { nom: "Cable chargeur (Type Android et Type-C)", categorieId: 10 },
      { nom: "Écouteur simple (avec fil)", categorieId: 10 },
      { nom: "Écouteur bluetooth (sans fil)", categorieId: 10 },
      { nom: "Foureaux smartphone", categorieId: 10 },
      { nom: "Téléphone incassable Tecno/Infinix", categorieId: 10 },
      { nom: "PowerBank 10 000mAh", categorieId: 10 },
      { nom: "PowerBank 20 000mAh", categorieId: 10 },
      { nom: "Chargeur universel de batterie (tas de 24)", categorieId: 10 },
      { nom: "Chargeur simple (sachet de 45)", categorieId: 10 },
      { nom: "Câble avec boitier", categorieId: 10 },
      { nom: "Batterie téléphone simple (1000 mAh)", categorieId: 10 },
      { nom: "Foureaux petit téléphone (plastique)", categorieId: 10 },
      { nom: "Téléphone en bouton (écran 1,52 pouce, batterie 1000mAh)", categorieId: 10 },
      { nom: "Carte mémoire 4GB", categorieId: 10 },
      { nom: "Colle pour plastification (1m)", categorieId: 10 },

      // Lot 11 : Mécanique
      { nom: "Pompe à vélo à main", categorieId: 11 },
      { nom: "Caisse à outils complet", categorieId: 11 },
      { nom: "Pneu de vélo (28 pouces ou 700mm)", categorieId: 11 },
      { nom: "Mojau à vélo (aluminium)", categorieId: 11 },
      { nom: "Chambre à air (28 pouces)", categorieId: 11 },
      { nom: "Jante (28 pouces)", categorieId: 11 },
      { nom: "Rayon (28 pouces)", categorieId: 11 },
      { nom: "Pièce à colle MF (paquet)", categorieId: 11 },
      { nom: "Dissolition (paquet)", categorieId: 11 },
      { nom: "Garre semi cuir (selle vélo)", categorieId: 11 },
      { nom: "Guidon vélo homme/femme", categorieId: 11 },
      { nom: "Caisse complète 182 outils mécanique auto/moto", categorieId: 11 },
      { nom: "Densimètre / pèse acide batterie", categorieId: 11 },
      { nom: "Sangle à cartouche métallique 500mm", categorieId: 11 },
      { nom: "Pied à coulisse 150mm", categorieId: 11 },
      { nom: "Parasolaire", categorieId: 11 },
      { nom: "Seringue à huile plastique 100ml", categorieId: 11 },

      // Lot 12 : Friperie
      { nom: "Ballot friperie homme (1er choix)", categorieId: 12 },
      { nom: "Ballot friperie femme (1er choix)", categorieId: 12 },
      { nom: "Ballot friperie enfant (1er choix)", categorieId: 12 }
    ];

    const tx = db.transaction("produits", "readwrite");
    const store = tx.objectStore("produits");

    produits.forEach(p => store.add(p));

    tx.oncomplete = () => console.log("✅ Lots 3 à 12 insérés !");
    tx.onerror = e => console.error("❌ Erreur insertion lots 3 à 12 :", e.target.error);
  };

  request.onerror = function(event) {
    console.error("Erreur ouverture DB :", event.target.error);
  };
})();
