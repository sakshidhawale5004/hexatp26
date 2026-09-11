const fs = require('fs');
const files = fs.readdirSync('.').filter(f => f.endsWith('.html'));

let updatedCount = 0;
files.forEach(f => {
  let content = fs.readFileSync(f, 'utf8');
  let changed = false;

  const insertionText = "\n        .cta h2, .cta p, .contact-footer h2, .contact-footer p, .contact-footer span { color: #ffffff !important; }\n";
  
  if (content.includes('.cta {') && !content.includes('.cta h2, .cta p, .contact-footer h2')) {
    if (content.includes('</style>')) {
       // Since there could be multiple </style> tags (e.g., inline svgs or other blocks),
       // it's better to replace the last occurrence or the one that contains our .cta rules.
       // A safer approach is to replace .contact-footer's closing brace.
       content = content.replace(/(\.contact-footer\s*\{[^}]*\})/, "$1" + insertionText);
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
