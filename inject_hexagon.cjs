const fs = require('fs');
const files = fs.readdirSync('.').filter(f => f.endsWith('.html'));

let updatedCount = 0;
files.forEach(f => {
  let content = fs.readFileSync(f, 'utf8');
  let changed = false;

  const scriptTag = '<script src="scripts/hexagon-bg.js"></script>';
  
  if (!content.includes(scriptTag)) {
    if (content.includes('</body>')) {
       content = content.replace('</body>', `    ${scriptTag}\n</body>`);
       changed = true;
    } else {
       // Append to end if </body> is missing for some reason
       content += `\n${scriptTag}\n`;
       changed = true;
    }
  }

  if (changed) {
    fs.writeFileSync(f, content);
    console.log('Updated ' + f);
    updatedCount++;
  }
});
console.log('Total updated files: ' + updatedCount);
