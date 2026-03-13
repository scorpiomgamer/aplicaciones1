const API_URL = "http://localhost:3000/api/productos/";

const productosContainer = document.getElementById('productos-container');
const loadingMessage = document.getElementById('loading-message');
const errorMessage = document.getElementById('error-message');

fetch(API_URL)
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Ocultar mensaje de carga
        loadingMessage.style.display = 'none';

        // Pintar los productos en el DOM
        if (data.success && data.daticos) {
            renderProductos(data.daticos);
        } else {
            throw new Error('Datos de productos no válidos');
        }
    })
    .catch(error => {
        // Ocultar mensaje de carga y mostrar error
        loadingMessage.style.display = 'none';
        errorMessage.style.display = 'block';
        console.error('Error al cargar productos:', error);
    });

function renderProductos(productos) {
    productosContainer.innerHTML = ''; // Limpiar contenedor

    productos.forEach(producto => {
        const productoHTML = `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card">
                    <img src="${producto.imagen_url || 'https://via.placeholder.com/300x200?text=Sin+Imagen'}" class="card-img-top" alt="${producto.nombre}">
                    <div class="card-body">
                        <h5 class="card-title">${producto.nombre}</h5>
                        <p class="card-text">${producto.descripcion || 'Sin descripción'}</p>
                        <p class="card-text"><strong>Precio: $${producto.precio}</strong></p>
                        <p class="card-text">Stock: ${producto.stock}</p>
                        <a href="" target="_blank">
                            <button type="button" class="boton">Ver más</button>
                        </a>
                    </div>
                </div>
            </div>
        `;
        productosContainer.innerHTML += productoHTML;
    });
}
