// public/js/reproductor.js

var player;
var intervalEscolta;      
var llistatPreguntes = [];
var preguntesRespondides = []; 
var preguntaActual = null;
var ultimTempsValid = 0;

// 🌟 1. LLEGIM L'ID DEL VÍDEO DES DE LA URL DEL NAVEGADOR (Ex: index.php?video=3)
const urlParams = new URLSearchParams(window.location.search);
const videoIdDeLaURL = urlParams.get('video');

var codiYouTubeDinamic = "";

function carregarPreguntesDelBackend() {
    if (!videoIdDeLaURL) {
        alert("No s'ha especificat cap ID de vídeo vàlid.");
        window.location.href = 'index.php';
        return;
    }

    // 🌟 2. CRIDEM AL TEU CONTROLADOR CENTRAL MVC
    fetch('index.php?action=api/get_preguntes&video_id=' + videoIdDeLaURL)
    .then(response => {
        if (!response.ok) throw new Error("Error en carregar el vídeo corporatiu");
        return response.json();
    })
    .then(data => {
        // Guardem les dades obtingudes dinàmicament de la Base de Dades
        llistatPreguntes = data.preguntes; 
        codiYouTubeDinamic = data.codi_youtube; 
        
        console.log("Dades del vídeo i preguntes carregades de la BBDD:", data);
        
        // Un cop tenim el codi del vídeo, inicialitzem l'Iframe de YouTube
        inicialitzarYouTubeAPI();
    })
    .catch(error => {
        console.error("Error carregant l'API:", error);
        alert("Error en accedir a aquest vídeo interactiu.");
    });
}

function inicialitzarYouTubeAPI() {
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
}

// Inicialitzem el procés quan es carrega la pàgina
window.onload = function() {
    carregarPreguntesDelBackend();
};

function onYouTubeIframeAPIReady() {
    player = new YT.Player('reproductor', {
        height: '450',
        width: '800',
        videoId: codiYouTubeDinamic, // 🌟 3. S'INJECTA EL CODI DINÀMIC DE LA BBDD!
        playerVars: { 'controls': 1, 'rel': 0, 'disablekb': 1 },
        events: { 'onStateChange': onPlayerStateChange }
    });
}

function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.PLAYING) {
        intervalEscolta = setInterval(comprovarTempsVideo, 500);
    } else {
        clearInterval(intervalEscolta);
    }
}

function comprovarTempsVideo() {
    var tempsActual = Math.floor(player.getCurrentTime());
    
    // Evitem que l'alumne salte cap endavant utilitzant la barra de progrés
    if (tempsActual > ultimTempsValid + 2) {
        player.seekTo(ultimTempsValid);
        return;
    }
    
    ultimTempsValid = tempsActual;

    // Busquem si hi ha alguna pregunta en aquest segon que no s'hagi respost encara
    let preguntaTrobada = llistatPreguntes.find(p => parseInt(p.segon) === tempsActual);
    
    if (preguntaTrobada && !preguntesRespondides.includes(preguntaTrobada.id)) {
        clearInterval(intervalEscolta);
        player.pauseVideo();
        preguntaActual = preguntaTrobada;
        mostrarModalPregunta(preguntaTrobada);
    }
}

function mostrarModalPregunta(pregunta) {
    preguntesRespondides.push(pregunta.id);
    const divContingut = document.getElementById('contingut-pregunta');
    
    let htmlFormulari = `<h3>${pregunta.titol}</h3><p>${pregunta.descripcio || ''}</p><form id='form-resposta'>`;

    if (pregunta.tipus === 'text') {
        htmlFormulari += `<textarea id='resp-text' required style='width:100%; height:80px; margin-bottom:15px;'></textarea>`;
    } else {
        pregunta.opcions.forEach(o => {
            let tipusInput = pregunta.tipus === 'single' ? 'radio' : 'checkbox';
            let nameInput = pregunta.tipus === 'single' ? 'resposta' : 'resposta[]';
            htmlFormulari += `<p><label><input type='${tipusInput}' name='${nameInput}' value='${o.id}'> ${o.text_opcio}</label></p>`;
        });
    }

    htmlFormulari += `<br><button type='submit' style='background:#1a365d; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; font-weight:bold;'>Enviar Resposta</button></form>`;
    
    divContingut.innerHTML = htmlFormulari;

    document.getElementById('modal-pregunta').style.display = 'block';
    document.getElementById('pantalla-fosca').style.display = 'block';

    document.getElementById('form-resposta').onsubmit = function(e) {
        e.preventDefault();
        enviarRespostaAlBackend();
    };
}

function enviarRespostaAlBackend() {
    let valorResposta = null;

    if (preguntaActual.tipus === 'text') {
        valorResposta = document.getElementById('resp-text').value;
    } else if (preguntaActual.tipus === 'single') {
        let opcioSeleccionada = document.querySelector("input[name='resposta']:checked");
        if (!opcioSeleccionada) { alert("Siusplau, tria una opció."); return; }
        valorResposta = opcioSeleccionada.value;
    } else if (preguntaActual.tipus === 'multiple') {
        let opcionsSeleccionades = document.querySelectorAll("input[name='resposta[]']:checked");
        if (opcionsSeleccionades.length === 0) { alert("Siusplau, tria almenys una opció."); return; }
        valorResposta = Array.from(opcionsSeleccionades).map(cb => cb.value);
    }

    // 🌟 4. ENVIEM LA RESPOSTA REAPROFITANT LA SESSIÓ ACTIVA DE L'ALUMNE LOGUEJAT
    fetch('index.php?action=api/post_resposta', {
        method: 'POST',
        headers: { 'Content-Type: application/json' },
        body: JSON.stringify({
            pregunta_id: preguntaActual.id,
            tipus: preguntaActual.tipus,
            resposta: valorResposta
        })
    })
    .then(res => res.json())
    .then(data => {
        console.log("Resposta guardada al servidor:", data);
        document.getElementById('modal-pregunta').style.display = 'none';
        document.getElementById('pantalla-fosca').style.display = 'none';
        player.playVideo();
    })
    .catch(err => {
        console.error("Error al guardar:", err);
        alert("Hi ha hagut un problema de connexió, però continuem.");
        document.getElementById('modal-pregunta').style.display = 'none';
        document.getElementById('pantalla-fosca').style.display = 'none';
        player.playVideo();
    });
}