const API_URL = "http://localhost:3000/api/productos/";

const productosContainer = document.getElementById('productos-container');
const loadingMessage = document.getElementById('loading-message');
const errorMessage = document.getElementById('error-message');

// Productos de respaldo para cuando el backend no responde
const productosFallback = [
    {
        nombre: "Producto demo 1",
        descripcion: "Producto de ejemplo para mostrar el cuadro.",
        precio: 0,
        stock: "-",
        imagen_url: ""
    },
    {
        nombre: "Producto demo 2",
        descripcion: "Otro producto de ejemplo sin imagen real.",
        precio: 0,
        stock: "-",
        imagen_url: ""
    },
    {
        nombre: "Producto demo 3",
        descripcion: "Ejemplo básico para completar la fila.",
        precio: 0,
        stock: "-",
        imagen_url: ""
    }
];

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
            // Si la API responde raro, usamos los productos de respaldo
            renderProductos(productosFallback);
        }
    })
    .catch(error => {
        // Ocultar mensaje de carga, mostrar error y usar fallback
        loadingMessage.style.display = 'none';
        errorMessage.style.display = 'block';
        console.error('Error al cargar productos:', error);
        renderProductos(productosFallback);
    });

function renderProductos(productos) {
    productosContainer.innerHTML = ''; // Limpiar contenedor

    productos.forEach(producto => {
        const productoHTML = `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card producto-card">
                    <img src="${producto.imagen_url || 'https://via.placeholder.com/300x200?text=Sin+Imagen'}" class="card-img-top" alt="${producto.nombre}">
                    <div class="card-body">
                        <h5 class="card-title">${producto.nombre}</h5>
                        <p class="card-text">${producto.descripcion || 'Sin descripción'}</p>
                        <p class="card-text"><strong>Precio: $${producto.precio}</strong></p>
                        <p class="card-text">Stock: ${producto.stock}</p>
                        <button type="button" class="boton">Ver más</button>
                    </div>
                </div>
            </div>
        `;
        productosContainer.innerHTML += productoHTML;
    });
}
