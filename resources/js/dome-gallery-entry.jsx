import React from 'react';
import ReactDOM from 'react-dom/client';
import DomeGallery from './components/DomeGallery';

const rootElement = document.getElementById('dome-gallery-root');
if (rootElement) {
  const rawPhotos = rootElement.getAttribute('data-photos');
  const photos = rawPhotos ? JSON.parse(rawPhotos) : [];

  const root = ReactDOM.createRoot(rootElement);
  root.render(
    <React.StrictMode>
      <div style={{ width: '100vw', height: '100vh', position: 'relative', overflow: 'hidden' }}>
        <DomeGallery 
          images={photos} 
          grayscale={false} 
          overlayBlurColor="#FFF8ED" 
        />
      </div>
    </React.StrictMode>
  );
}
