// routes/auth.routes.js
const express = require('express');
const router = express.Router();
const authController = require('../controllers/auth.controller');  // Importa el controlador de autenticación

// Ruta de registro
router.post('/register', authController.register);

// Ruta de inicio de sesión (login)
router.post('/login', authController.login);

module.exports = router;
