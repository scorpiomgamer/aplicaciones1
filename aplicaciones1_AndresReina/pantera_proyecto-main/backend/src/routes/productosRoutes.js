const express  = require('express')
const router = express.Router()
const ProductoController = require('../controllers/ProductoController')

router.get('/',ProductoController.obtenerProductos)
router.get('/:id',ProductoController.obtenerPorId)


module.exports = router
