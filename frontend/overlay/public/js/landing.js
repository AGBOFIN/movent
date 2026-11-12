(function(){
var tiles=['#e2703a','#d9a441','#9ab584','#c96f4a','#7a8c5f','#b4552d','#8a5a44','#5f7a52'],ic=['🎤','🎟️','🎸','🎧','🎉','🎭','🏆','📸'];
var t=document.getElementById('trk'),h='';
for(var k=0;k<2;k++)for(var i=0;i<tiles.length;i++)h+='<div class="tile" style="background:'+tiles[i]+'">'+ic[i]+'</div>';
t.innerHTML=h;
function acc(id,items){
  var el=document.getElementById(id),s='';
  items.forEach(function(it,i){s+='<div class="it'+(i===0?' open':'')+'"><button type="button" aria-expanded="'+(i===0)+'"><span>'+it[0]+'</span><span class="pl">+</span></button><div class="bd">'+it[1]+'</div></div>'});
  el.innerHTML=s;
  el.addEventListener('click',function(e){
    var b=e.target.closest('button');if(!b)return;
    var it=b.parentNode,was=it.classList.contains('open');
    el.querySelectorAll('.it').forEach(function(x){x.classList.remove('open');x.querySelector('button').setAttribute('aria-expanded','false')});
    if(!was){it.classList.add('open');b.setAttribute('aria-expanded','true')}
  });
}
acc('acc1',[
 ['Découverte d’événements','Parcourez le catalogue public, filtrez par catégorie et ouvrez la fiche détaillée de chaque événement validé.'],
 ['Réservation simple','Choisissez la quantité, payez en mode simulé et suivez vos réservations depuis votre espace.'],
 ['Billets QR uniques','Un billet PDF avec un code QR unique est généré dès que la réservation est confirmée.'],
 ['Validation par l’équipe','Chaque événement est examiné par un administrateur avant d’être visible publiquement.'],
 ['Zéro surréservation','Les places sont bloquées de façon atomique : la capacité n’est jamais dépassée.']
]);
acc('acc2',[
 ['Outils pour les organisateurs','Créez, modifiez et soumettez vos événements, puis suivez leur état de validation en temps réel.'],
 ['Billets sans friction','Paiement simulé, confirmation immédiate et billets disponibles dans votre espace participant.'],
 ['Une communauté engagée','Participants, organisateurs et administrateurs travaillent ensemble dans un espace clair et sûr.'],
 ['Un avenir centré sur les événements','Mobile Money, notifications, scan des QR codes à l’entrée : la suite est déjà prévue.']
]);
})();