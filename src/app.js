const express = require('express')
require('dotenv').config()
const cors = require('cors')
const ProductosRouter = require('./routes/productosRoutes')
const APP = express()
const PORT = process.env.PORT || 3000
APP.use(cors())
APP.get('/',(request, response) => {
    response.json({
        mensaje: "Funciona mi API"
    })
})

APP.use('/api/productos',ProductosRouter)

APP.listen(PORT, () => {
    console.log(`servidor corriendo ${PORT}`);
});
