<?php
declare(strict_types=1);
$title = 'Inicio';
$cssFile = 'frontend/css/style.css';
require __DIR__ . '/includes/header.php';
?>

<div id="carouselExample" class="carousel slide">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img class="imagenes" src="frontend/assets/Disponible-05.jpg" alt="Disponible 05">
    </div>
    <div class="carousel-item">
      <img class="imagenes" src="frontend/assets/Disponible-07.jpg" alt="Disponible 07">
    </div>
    <div class="carousel-item">
      <img class="imagenes" src="frontend/assets/Disponible-08.jpg" alt="Disponible 08">
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>

<div class="mitad">
  <div class="container py-4 d-flex flex-wrap gap-4 align-items-center">
    <img class="imagen_princi" src="frontend/assets/PANTHERA%20rosapng-07.png" alt="Panthera">
    <div class="mitad2 p-4">
      <h1 class="cuales">Quiénes somos</h1>
      <p>Somos un emprendimiento que busca llegar más alto junto a la venta de accesorios para dama o regalos.</p>
      <h2 class="h4 mt-4">Nuestro objetivo</h2>
      <p>Lograr progresar y seguir adelante; si confías en tus sueños llegarás muy alto y ese es nuestro objetivo.</p>
    </div>
  </div>
</div>

<div class="galeria py-4">
  <div class="container">
    <div class="row g-3">
      <div class="col-lg-4">
        <div class="card card-soft">
          <img class="productos" src="frontend/assets/Disponible-09.jpg" alt="Protector audífonos">
          <div class="card-body">
            <h5 class="card-title">Protector para audífonos inalámbricos</h5>
            <p class="card-text">Protector con temática de ositos cariñositos.</p>
            <a href="productos.php" class="btn btn-primary">Ver producto</a>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card card-soft">
          <img class="productos" src="frontend/assets/Disponible-06.jpg" alt="Bolso de malla">
          <div class="card-body">
            <h5 class="card-title">Bolso de malla</h5>
            <p class="card-text">Bolso de mano en forma de malla para cualquier outfit.</p>
            <a href="productos.php" class="btn btn-primary">Ver producto</a>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card card-soft">
          <img class="productos" src="frontend/assets/Disponible-08.jpg" alt="Par de medias">
          <div class="card-body">
            <h5 class="card-title">Par de medias</h5>
            <p class="card-text">Par de medias con temática de ositos cariñositos.</p>
            <a href="productos.php" class="btn btn-primary">Ver producto</a>
          </div>
        </div>
      </div>
    </div>

    <div class="text-center mt-4">
      <a class="btn btn-outline-dark" href="productos.php">Más productos</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

