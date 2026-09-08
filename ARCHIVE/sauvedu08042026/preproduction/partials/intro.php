<svg width="0" height="0" style="position: absolute;">
    <linearGradient id="goldGradientSvg" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" style="stop-color:#bf953f;stop-opacity:1" />
        <stop offset="50%" style="stop-color:#fcf6ba;stop-opacity:1" />
        <stop offset="100%" style="stop-color:#b38728;stop-opacity:1" />
    </linearGradient>
</svg>

<div id="intro-overlay">
    <div class="intro-content">
        <div class="intro-stars-wrapper">
            <?php for($i=1; $i<=5; $i++): ?>
            <svg class="intro-star-svg star-<?php echo $i; ?>" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
            <?php endfor; ?>
        </div>
        <div class="intro-line intro-line-top"></div>
        <div class="intro-title-wrapper">
            <h1 class="intro-main-title">Bellevue d'Aveyron</h1>
            <div class="intro-shimmer"></div>
        </div>
        <div class="intro-line intro-line-bottom"></div>
        <div class="intro-subtitle">Villa de Luxe 5 Étoiles</div>
    </div>
</div>