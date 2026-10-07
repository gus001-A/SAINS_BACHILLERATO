<?php

return [
    /*
    | Video que explica cómo subir los documentos (botón ▶ en "Mi perfil").
    | Acepta un enlace de YouTube o Vimeo. Si se deja vacío y existe el archivo
    | public/videos/tutorial-documentos.mp4, se usa ese archivo.
    */
    'video_documentos' => env('VIDEO_TUTORIAL_DOCUMENTOS'),

    /* Tamaños del examen de prueba y del examen para certificar (número de preguntas). */
    'simulador_tamanos' => [50, 150, 250],
    'certificacion_preguntas' => 250,
];
