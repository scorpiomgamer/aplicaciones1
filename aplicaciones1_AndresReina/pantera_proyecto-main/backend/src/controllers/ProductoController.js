const { response } = require('express')
const ProductoModel = require('../models/ProductoModel')

class ProductoController{
//get - para obtener todos mis productos          
    static async obtenerProductos(request, response){
        const productos = await ProductoModel.obtenerProductos()
        response.json({
            success: true,
            daticos: productos
        })
    }
    static async obtenerPorId(request,response){
        try{
            const {id} = request.params
            const producto = await ProductoModel.obtenerPorId(id)
            if(!producto){
              return response.status(404).json({
                    success: false,
                    mensaje: 'el producto no existe perro!!!'
                })
            }else{
                response.json(producto)

            }
        }catch(error){
            throw error
        }



    }


}

module.exports = ProductoController
