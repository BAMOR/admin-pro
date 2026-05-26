<?php
function mostrarAlerta($titulo, $mensaje, $icono = 'success', $redireccionar = null) {
    echo "<script>
        Swal.fire({
            title: '$titulo',
            text: '$mensaje',
            icon: '$icono',
            confirmButtonText: 'Aceptar'
        })";
    
    if ($redireccionar) {
        echo ".then((result) => {
            if (result.isConfirmed) {
                window.location.href = '$redireccionar';
            }
        });";
    }
    
    echo "</script>";
}
?>