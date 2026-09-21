const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const regex = /var d = Math\.floor\(diff \/ 86400000\);[\s\S]*?el\.textContent = d \+ 'd ' \+ h \+ 'h ' \+ m \+ 'm ' \+ s \+ 's';/;

const newLogic = `var days = Math.ceil(diff / 86400000);
                        el.textContent = days + ' DAYS';`;

if (regex.test(html)) {
    html = html.replace(regex, newLogic);
    
    // Also remove pad2 since it's no longer needed
    const pad2Regex = /function pad2\(num\) \{[\s\S]*?\}\s*var tickerInitialized = false;/;
    html = html.replace(pad2Regex, 'var tickerInitialized = false;');
    
    fs.writeFileSync('index.html', html, 'utf8');
    console.log('Removed hours/mins/secs and restored just DAYS');
} else {
    console.log('regex not found');
}
