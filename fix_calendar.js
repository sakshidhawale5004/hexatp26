const fs = require('fs');
let html = fs.readFileSync('tp-compliance-calendar.html', 'utf8');

html = html.replace(
  '.tpc-upcoming-track{display:flex;width:max-content;animation:marquee 60s linear infinite;}',
  '.tpc-upcoming-track{display:flex;width:max-content;animation:marquee 25s linear infinite;}'
);

const searchStr = 'wrap.innerHTML = upcoming.map(e=>`\n        <div class="tpc-chip ${cssClass}">\n          <div class="days" data-target="${e.ndMs}" data-diff="${e.diff}">${e.diff===0?\'Today\':\'\'}</div>\n          <div class="label">${e.title}</div>\n          <div class="country">${countryMeta(e.country).flag} ${countryMeta(e.country).name}</div>\n        </div>`).join(\'\');';

const replaceStr = 'const itemsHtml = upcoming.map(e=>`\n        <div class="tpc-chip ${cssClass}">\n          <div class="days" data-target="${e.ndMs}" data-diff="${e.diff}">${e.diff===0?\'Today\':\'\'}</div>\n          <div class="label">${e.title}</div>\n          <div class="country">${countryMeta(e.country).flag} ${countryMeta(e.country).name}</div>\n        </div>`).join(\'\');\n      wrap.innerHTML = itemsHtml + itemsHtml;';

if (html.indexOf(searchStr) !== -1) {
    html = html.replace(searchStr, replaceStr);
    fs.writeFileSync('tp-compliance-calendar.html', html, 'utf8');
    console.log('Successfully replaced!');
} else {
    console.log('Search string not found. Here is what we found around wrap.innerHTML:');
    const idx = html.indexOf('wrap.innerHTML = upcoming');
    if (idx !== -1) {
        console.log(html.substring(idx, idx + 500));
    }
}
