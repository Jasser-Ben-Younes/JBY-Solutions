document.addEventListener("DOMContentLoaded", () => {
  console.log("JBY Solutions site loaded");
});

function initWaves() {
  const canvas = document.getElementById("waves");
  if (!canvas) return;

  const ctx = canvas.getContext("2d");

  // Responsive sizing
  function resizeCanvas() {
    const container = canvas.closest('.wave-container');
    const rect = container.getBoundingClientRect();
    
    canvas.width = rect.width * window.devicePixelRatio;
    canvas.height = rect.height * window.devicePixelRatio;
    
    ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  }

  resizeCanvas();
  window.addEventListener('resize', resizeCanvas);

  let width = canvas.clientWidth;
  let height = canvas.clientHeight + 30;

  // Scale params based on size
  const aspectRatio = width / height;
  const amplitude = Math.min(25, height * 0.25);
  const wavelength = Math.max(300, width * 0.8);
  const speed = 0.003;
  const letterOffset = Math.min(18, height * 0.18);
  
  let t = 0;

  function waveY(x, phase) {
    return height / 2 + amplitude *
      Math.sin((x / wavelength) * 2 * Math.PI + t + phase);
  }

  function waveSlope(x, phase) {
    return amplitude * (2 * Math.PI / wavelength) *
      Math.cos((x / wavelength) * 2 * Math.PI + t + phase);
  }

  function drawWave(phase, color) {
    ctx.beginPath();
    ctx.strokeStyle = color;
    ctx.lineWidth = Math.max(0.8, height / 100);
    
    for (let x = 0; x < width; x++) {
      const y = waveY(x, phase);
      if (x === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    }
    ctx.stroke();
  }

  function drawLetter(letter, x, phase, color) {
    const y = waveY(x, phase) - letterOffset;
    const slope = waveSlope(x, phase);
    const angle = Math.atan(slope);

    ctx.save();
    ctx.translate(x, y);
    ctx.rotate(angle);
    
    ctx.fillStyle = color;
    const fontSize = Math.max(20, Math.min(45, height * 0.45));
    ctx.font = `${fontSize}px monospace`;
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    
    ctx.fillText(letter, 0, 0);
    ctx.restore();
  }

  function animate() {
    ctx.clearRect(0, 0, width, height);

    drawWave(0, "#818181");
    drawWave(2 * Math.PI / 3, "#818181");
    drawWave(4 * Math.PI / 3, "#818181");

    // Responsive letter positions
    const spacing = width / 4;
    drawLetter("J", spacing, 0, "#ffffff");
    drawLetter("B", spacing * 2, 2 * Math.PI / 3, "#ffffff");
    drawLetter("Y", spacing * 3, 4 * Math.PI / 3, "#ffffff");

    t += speed;
    requestAnimationFrame(animate);
  }

  animate();
}
