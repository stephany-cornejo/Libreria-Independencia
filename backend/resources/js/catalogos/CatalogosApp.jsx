
import CategoriasListado from './categorias/CategoriasListado';
import ProveedoresListado from './proveedores/ProveedoresListado';

export default function CatalogosApp() {
    const ruta = window.location.pathname.replace(/\/+$/, '');

    if (ruta === '/panel/proveedores') {
        return <ProveedoresListado />;
    }

    return <CategoriasListado />;
}
