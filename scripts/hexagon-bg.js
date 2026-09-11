document.addEventListener("DOMContentLoaded", () => {
    // Inject CSS
    const style = document.createElement('style');
    style.innerHTML = `
        .hex-bg-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            z-index: 999; /* Place above content so it's visible throughout */
            pointer-events: none;
            perspective: 1000px;
        }
        .hex-3d {
            position: absolute;
            background: linear-gradient(135deg, rgba(245, 196, 0, 0.3), rgba(245, 196, 0, 0.05));
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            animation: floatHex 20s infinite linear;
            transform-style: preserve-3d;
            bottom: -150px;
        }
        @keyframes floatHex {
            0% {
                transform: translateY(0) translateX(0) rotateX(0deg) rotateY(0deg) rotateZ(0deg);
                opacity: 0;
            }
            10% { opacity: 0.8; }
            90% { opacity: 0.8; }
            100% {
                transform: translateY(-120vh) translateX(100px) rotateX(360deg) rotateY(360deg) rotateZ(180deg);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Inject HTML container
    const container = document.createElement('div');
    container.className = 'hex-bg-container';
    document.body.appendChild(container);

    // Create hexagons
    const hexCount = 15; // Number of floating hexagons
    for (let i = 0; i < hexCount; i++) {
        const hex = document.createElement('div');
        hex.className = 'hex-3d';
        
        // Randomize size, position, and animation
        const size = Math.random() * 60 + 40; // 40px to 100px
        const left = Math.random() * 100; // 0% to 100%
        const delay = Math.random() * 20; // 0s to 20s
        const duration = Math.random() * 10 + 15; // 15s to 25s

        hex.style.width = `${size}px`;
        hex.style.height = `${size}px`;
        hex.style.left = `${left}vw`;
        hex.style.animationDelay = `${delay}s`;
        hex.style.animationDuration = `${duration}s`;

        container.appendChild(hex);
    }
});
