document.addEventListener("DOMContentLoaded", () => {
  console.log("JBY Solutions site loaded");
});


function initWaves() {
  const canvas = document.getElementById("waves");
  if (!canvas) return;

  const ctx = canvas.getContext("2d");
  const width = canvas.width;
  const height = canvas.height;

  const amplitude = 80;
  const wavelength = 1000;
  const speed = 0.001;
  const letterOffset = 35;
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
    ctx.lineWidth = 3;
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
    ctx.font = "100px monospace";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(letter, 0, 0);
    ctx.restore();
  }

  function animate() {
    ctx.clearRect(0, 0, width, height);

    drawWave(0, "#747474");
    drawWave(2 * Math.PI / 3, "#747474");
    drawWave(4 * Math.PI / 3, "#747474");

    drawLetter("J", width * 0.2, 0, "#747474");
    drawLetter("B", width * 0.5, 2 * Math.PI / 3, "#747474");
    drawLetter("Y", width * 0.8, 4 * Math.PI / 3, "#747474");

    t += speed;
    requestAnimationFrame(animate);
  }

  animate();
}