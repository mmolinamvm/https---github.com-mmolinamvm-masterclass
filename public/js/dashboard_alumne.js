// public/js/dashboard_alumne.js

document.addEventListener('DOMContentLoaded', function() {
    // Demanem els vídeos a la nova API de manera centralitzada pel Front Controller
    fetch('index.php?action=api/get_videos_alumne', { 
        credentials: 'include' // Imprescindible per enviar les cookies de sessió PHP
    })
    .then(response => {
        if (!response.ok) throw new Error("No s'han pogut carregar les assignacions.");
        return response.json();
    })
    .then(data => {
        // Pintem el nom real de l'alumne retornat per la sessió del backend
        document.getElementById('nom-alumne').innerText = data.usuari_nom;
        
        const contenidor = document.getElementById('contenidor-videos'); 
        contenidor.innerHTML = '';
        
        if (data.videos.length === 0) {
            contenidor.innerHTML = '<p>Actualment no tens cap vídeo assignat.</p>';
            return;
        }

        // Iterem sobre cada vídeo per construir la seva targeta de forma dinàmica
        data.videos.forEach(v => {
            let estatVisual = v.estat;
            let textBadge = v.estat;
            let estilBadge = `badge-${v.estat}`;
            let deshabilitat = false;

            // Control de restricció: Vídeo caducat en data
            if (parseInt(v.esta_caducat) === 1) {
                estatVisual = 'caducat';
                textBadge = 'Caducat';
                estilBadge = 'badge-caducat';
                deshabilitat = true; 
            }

            // Control de restricció: S'han esgotat els intents d'aquest alumne
            if (parseInt(v.reproduccions_restants) <= 0) {
                textBadge = 'Exhaurit';
                deshabilitat = true;
            }

            // Construcció del component HTML de la Card
            const card = document.createElement('div');
            card.className = `card-video estat-${estatVisual}`;
            card.innerHTML = `
                <div>
                    <span class="badge ${estilBadge}">${textBadge}</span>
                    <h3 class="titol-video">${v.titol}</h3>
                    <p class="desc-video">${v.descripcio || 'Sense descripció.'}</p>
                    <div class="info-repros">
                        🔄 Reproduccions restants: <strong>${v.reproduccions_restants}</strong><br>
                        📅 Data límit: ${v.data_limit ? new Date(v.data_limit).toLocaleDateString() : 'Sense límit'}
                    </div>
                </div>
                <a href="index.php?video=${v.id}" class="btn-accedir ${deshabilitat ? 'btn-disabled' : ''}">
                    ${deshabilitat ? 'No disponible' : 'Mirar Vídeo'}
                </a>
            `;
            contenidor.appendChild(card);
        });
    })
    .catch(error => {
        console.error("Error al Dashboard:", error);
        document.getElementById('contenidor-videos').innerHTML = 
            '<p style="color:red;">Error en carregar les dades del panell. Torna-ho a provar més tard.</p>';
    });
});