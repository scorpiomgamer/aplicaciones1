const DB = require('../config/database')

class ProductoModel {
    //obtener productos
    static async obtenerProductos(){
        const [rows] = await DB.query('SELECT * FROM productos')
        return rows
    }
    //obtener 1 solo producto por id
    static async obtenerPorId(id){
        const [row] = await DB.query('SELECT * FROM productos WHERE id_producto = ?',id)
        return row
    }

}

module.exports = ProductoModel
