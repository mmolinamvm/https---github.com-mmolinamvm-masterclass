var player;              
var intervalEscolta;      
var llistatPreguntes = [];
var preguntesRespondides = []; 
var preguntaActual = null;
var ultimTempsValid = 0; 
var codiYouTubeActual = "";

// Captura el paràmetre ?video=X de la URL del navegador de l'alumne
const urlParams = new URLSearchParams(window.location.search);
const videoIdActual = urlParams.get('video') || 1; // Si no hi ha cap, per defecte posem el 1

window.onload = function() {
    inicialitzarYouTubeAPI();
};

function inicialitzarYouTubeAPI() {
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
}

// PAS 2: YouTube es descarrega i crida aquesta funció automàticament.
// En lloc de crear el player directament, primer anem a buscar les dades al backend!
function onYouTubeIframeAPIReady() {
    console.log("API de YouTube a punt. Sol·licitant dades del vídeo...");
    carregarPreguntesDelBackend();
}

// PAS 3: El backend respon amb el codi de YouTube i les preguntes
function carregarPreguntesDelBackend() {
    fetch(`index.php?action=api/get_preguntes&video_id=${videoIdActual}`) 
        .then(response => {
            if (!response.ok) throw new Error("No s'han pogut carregar les dades del vídeo");
            return response.json();
        })
        .then(data => {
            console.log("Dades rebudes del backend:", data);

            // Guardem de forma segura la informació del vídeo i les preguntes
            codiYouTubeActual = data.codi_youtube; 
            llistatPreguntes = data.preguntes || []; 
            
            if(data.titol) {
                document.querySelector('h1').innerText = data.titol;
            }

            // PAS 4: Ara que tenim el codiYouTubeActual de veritat, instanciem el reproductor!
            crearReproductorYouTube();
        })
        .catch(error => {
            console.error("Error carregant l'API del backend:", error);
        });
}

// PAS 5: Funció aïllada que munta el Player de forma segura
function crearReproductorYouTube() {
    if (!codiYouTubeActual) {
        console.error("Error: No es pot crear el reproductor sense un codi de YouTube vàlid.");
        return;
    }

    player = new YT.Player('reproductor', {
        height: '360',
        width: '640',
        videoId: codiYouTubeActual, // Ara està garantit que té el valor de la BD
        playerVars: { 'controls': 1, 'rel': 0 },
        events: { 'onStateChange': onPlayerStateChange }
    });
    console.log("Reproductor instanciat amb el vídeo:", codiYouTubeActual);
}

function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.PLAYING) {
        intervalEscolta = setInterval(comprovarTemps, 500);
    } else {
        clearInterval(intervalEscolta);
    }
}

function comprovarTemps() {
    var tempsActual = player.getCurrentTime();
    var segonActual = Math.floor(tempsActual);
    
    llistatPreguntes.forEach(function(p) {
        if (!preguntesRespondides.includes(p.id) && ultimTempsValid < p.segon && segonActual > p.segon) {
            player.seekTo(p.segon); 
            segonActual = p.segon;
        }
    });
    
    ultimTempsValid = tempsActual;

    var trobada = llistatPreguntes.find(function(p) {
        return p.segon === segonActual && !preguntesRespondides.includes(p.id);
    });

    if (trobada && document.getElementById('modal-pregunta').style.display !== 'block') {
        llançarPregunta(trobada);
    }
}

function llançarPregunta(pregunta) {
    clearInterval(intervalEscolta); 
    player.pauseVideo();            
    preguntaActual = pregunta; 

    document.getElementById('enunciat').innerText = pregunta.text;
    var contenidorForm = document.getElementById('contenidor-formulari');
    contenidorForm.innerHTML = ""; 

    if (pregunta.tipus === "text") {
        contenidorForm.innerHTML = '<input type="text" id="resposta-text" autofocus placeholder="Escriu la teva resposta aquí...">';
    } else if (pregunta.tipus === "single") {
        pregunta.opcions.forEach(function(opcio) {
            contenidorForm.innerHTML += `
                <label class="opcio-bloc">
                    <input type="radio" name="opcio-single" value="${opcio.id}"> <span>${opcio.text_opcio}</span>
                </label>`;
        });
    } else if (pregunta.tipus === "multiple") {
        pregunta.opcions.forEach(function(opcio) {
            contenidorForm.innerHTML += `
                <label class="opcio-bloc">
                    <input type="checkbox" name="opcio-multiple" value="${opcio.id}"> <span>${opcio.text_opcio}</span>
                </label>`;
        });
    }

    document.getElementById('modal-pregunta').style.display = 'block';
    document.getElementById('pantalla-fosca').style.display = 'block';
}

function continuarVideo() {
    var respostaGuardar = null;

    if (preguntaActual.tipus === "text") {
        var textInput = document.getElementById('resposta-text').value;
        if (textInput.trim() === "") return alert("Respon la pregunta de text.");
        respostaGuardar = textInput;
    } else if (preguntaActual.tipus === "single") {
        var seleccionat = document.querySelector('input[name="opcio-single"]:checked');
        if (!seleccionat) return alert("Selecciona una opció.");
        respostaGuardar = parseInt(seleccionat.value);
    } else if (preguntaActual.tipus === "multiple") {
        var seleccionats = document.querySelectorAll('input[name="opcio-multiple"]:checked');
        if (seleccionats.length === 0) return alert("Selecciona com a mínim una opció.");
        
        respostaGuardar = [];
        seleccionats.forEach(function(cb) {
            respostaGuardar.push(parseInt(cb.value));
        });
    }

    var dadesAEnviar = {
        pregunta_id: preguntaActual.id,
        tipus: preguntaActual.tipus,
        resposta: respostaGuardar
    };

    fetch('index.php?action=api/post_resposta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dadesAEnviar)
    })
    .then(response => response.json())
    .then(data => {
        console.log("Èxit de resposta desar:", data);
        preguntesRespondides.push(preguntaActual.id);
        document.getElementById('modal-pregunta').style.display = 'none';
        document.getElementById('pantalla-fosca').style.display = 'none';
        player.playVideo();
    })
    .catch(error => {
        console.error("Error xarxa:", error);
        alert("Error desant dades. Revisa el servidor backend.");
    });
}