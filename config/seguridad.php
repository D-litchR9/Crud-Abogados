<?php
// Preguntas de seguridad disponibles en todo el sistema
const PREGUNTAS_SEGURIDAD = [
    '¿Cuál es el nombre de tu primera mascota?',
    '¿En qué ciudad naciste?',
    '¿Cuál es el nombre de tu mejor amigo de la infancia?',
    '¿Cuál es tu comida favorita?',
    '¿Cuál es el nombre de la empresa?'
];

// Quita espacios y pasa a minúsculas, para que "Malvados " y "malvados" valgan igual
function normalizar_respuesta($texto) {
    return mb_strtolower(trim($texto), 'UTF-8');
}