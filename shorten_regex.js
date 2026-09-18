const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

html = html.replace('desc: \'CT/income tax return due (incl. TP disclosure)\'', 'desc: \'CT return & TP disclosure\'');
html = html.replace(/desc: 'Local File deadline'/g, 'desc: \'Local File\'');
html = html.replace(/desc: 'TP disclosure \/ annual filing due'/g, 'desc: \'TP filing due\'');
html = html.replace(/desc: 'Master File deadline'/g, 'desc: \'Master File\'');

// Also increase speed (decrease time) as requested: "speed of ticker can be increase"
// I will change it from 35s to 25s
html = html.replace('animation: ticker-marquee 35s linear infinite;', 'animation: ticker-marquee 25s linear infinite;');
html = html.replace('animation: ticker-marquee 15s linear infinite;', 'animation: ticker-marquee 25s linear infinite;');

fs.writeFileSync('index.html', html, 'utf8');
console.log('Descriptions shortened and speed increased to 25s');
