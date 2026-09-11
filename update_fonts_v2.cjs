const fs = require('fs');
const files = fs.readdirSync('.').filter(f => f.endsWith('.html'));

let updatedCount = 0;
files.forEach(f => {
  let content = fs.readFileSync(f, 'utf8');
  let changed = false;

  const insertionText = "\n<style>.cta h2, .cta p, .contact-footer h2, .contact-footer p, .contact-footer span { color: #ffffff !important; }</style>\n";
  
  if ((content.includes('class="cta"') || content.includes('class="contact-footer"')) && !content.includes('.cta h2, .cta p, .contact-footer h2')) {
    if (content.includes('</head>')) {
       content = content.replace('</head>', insertionText + '</head>');
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
