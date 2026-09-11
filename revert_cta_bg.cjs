const fs = require('fs');
const files = fs.readdirSync('.').filter(f => f.endsWith('.html'));

let updatedCount = 0;
files.forEach(f => {
  let content = fs.readFileSync(f, 'utf8');
  let changed = false;

  // 1. Revert .cta background to white
  const ctaBgRegex = /(\.cta\s*\{[^}]*)background:\s*url\('88298\.jpg'\)[^;]+;/g;
  if (ctaBgRegex.test(content)) {
    content = content.replace(ctaBgRegex, "$1background: #ffffff;");
    changed = true;
  }

  // 2. Remove .cta from the injected white font color rule
  const oldStyle = "<style>.cta h2, .cta p, .contact-footer h2, .contact-footer p, .contact-footer span { color: #ffffff !important; }</style>";
  const newStyle = "<style>.contact-footer h2, .contact-footer p, .contact-footer span { color: #ffffff !important; }</style>";
  if (content.includes(oldStyle)) {
    content = content.replace(oldStyle, newStyle);
    changed = true;
  }

  // If there's an older formatting of the style injected in update_fonts.cjs
  const oldStyle2 = ".cta h2, .cta p, .contact-footer h2, .contact-footer p, .contact-footer span { color: #ffffff !important; }";
  const newStyle2 = ".contact-footer h2, .contact-footer p, .contact-footer span { color: #ffffff !important; }";
  if (content.includes(oldStyle2) && !content.includes(oldStyle)) {
    content = content.replace(oldStyle2, newStyle2);
    changed = true;
  }

  if (changed) {
    fs.writeFileSync(f, content);
    console.log('Updated ' + f);
    updatedCount++;
  }
});
console.log('Total updated files: ' + updatedCount);
