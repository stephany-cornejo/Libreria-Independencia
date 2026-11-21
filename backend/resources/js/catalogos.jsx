
import { createRoot } from 'react-dom/client';
import CatalogosApp from './catalogos/CatalogosApp';

const contenedor = document.getElementById('catalogos-react');

if (contenedor) {
    const root = createRoot(contenedor);

    root.render(<CatalogosApp />);
}
