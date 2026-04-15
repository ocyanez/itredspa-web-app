// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  -------------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa buscar_producto .JS ----------------------------------------
    ------------------------------------------------------------------------------------------------------------------- */



// Autocomplete Productos

    document.addEventListener('DOMContentLoaded', () => {

        let indiceActivo = -1;

        document.addEventListener('input', async (e) => {
            if (!e.target.classList.contains('autocomplete-producto')) return;

            const input = e.target;
            const contenedor = input.closest('.producto-cell');
            const lista = contenedor.querySelector('.autocomplete-list');
            const texto = input.value.trim();

            lista.innerHTML = '';
            indiceActivo = -1;

            if (texto.length < 2) return;

            try {
                const res = await fetch(`/php/ingreso_ventas/registro_ventas/buscar_productos.php?q=${encodeURIComponent(texto)}`)


                if (!res.ok) {
                console.error('Error HTTP:', res.status);
                return;
            }

                const productos = await res.json();

                if (!Array.isArray(productos)) {
                console.error('Respuesta no es array', productos);
                return;
            }

                productos.forEach((item, index) => {
                    const div = document.createElement('div');
                    div.className = 'autocomplete-item';
                    div.textContent = item.producto;

                    div.addEventListener('click', () => {
                        input.value = item.producto;
                        lista.innerHTML = '';
                    });

                    lista.appendChild(div);
                });

            } catch (err) {
                console.error('Error buscando productos:', err);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (!e.target.classList.contains('autocomplete-producto')) return;

            const input = e.target;
            const lista = input.closest('.producto-cell').querySelector('.autocomplete-list');
            const items = lista.querySelectorAll('.autocomplete-item');

            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                indiceActivo = (indiceActivo + 1) % items.length;
                actualizarActivo(items);
            }

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                indiceActivo = (indiceActivo - 1 + items.length) % items.length;
                actualizarActivo(items);
            }

            if (e.key === 'Enter') {
                e.preventDefault();
                if (indiceActivo >= 0) {
                    input.value = items[indiceActivo].textContent;
                    lista.innerHTML = '';
                }
            }
        });

        document.addEventListener('click', (e) => {
            document.querySelectorAll('.autocomplete-list').forEach(lista => {
                if (!lista.contains(e.target)) {
                    lista.innerHTML = '';
                }
            });
        });

        function actualizarActivo(items) {
            items.forEach((item, i) => {
                item.classList.toggle('active', i === indiceActivo);
            });
        }

    });




/*  --------------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa buscar_producto .JS -------------------------------------------
    -------------------------------------------------------------------------------------------------------------------- */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ