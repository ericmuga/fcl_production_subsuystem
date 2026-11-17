import JSZip from 'jszip';

const mainNamespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
const relationshipNamespace = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
const packageNamespace = 'http://schemas.openxmlformats.org/package/2006/relationships';
const contentNamespace = 'http://schemas.openxmlformats.org/package/2006/content-types';

function parseXml(text) {
    const xml = new DOMParser().parseFromString(text, 'application/xml');
    if (xml.getElementsByTagName('parsererror').length) {
        throw new Error('The workbook contains invalid XML.');
    }
    return xml;
}

async function readFile(zip, path) {
    const file = zip.file(path);
    if (!file) {
        throw new Error('The workbook is missing a required Excel file.');
    }
    return file.async('string');
}

function resolveTarget(target) {
    const url = new URL(target, 'https://workbook.invalid/xl/workbook.xml');
    if (url.origin !== 'https://workbook.invalid' || !url.pathname.startsWith('/xl/')) {
        throw new Error('The workbook contains an invalid sheet path.');
    }
    return decodeURIComponent(url.pathname.slice(1));
}

async function extractForecast(file) {
    const input = await JSZip.loadAsync(await file.arrayBuffer());
    const workbook = parseXml(await readFile(input, 'xl/workbook.xml'));
    const relationships = parseXml(await readFile(input, 'xl/_rels/workbook.xml.rels'));
    const sheets = Array.from(workbook.getElementsByTagNameNS(mainNamespace, 'sheet'));
    const sheet = sheets.find(item => item.getAttribute('name') === 'Forecast By SKU');
    if (!sheet) {
        throw new Error('The workbook must contain a Forecast By SKU sheet.');
    }
    const relations = Array.from(relationships.getElementsByTagNameNS(packageNamespace, 'Relationship'));
    const sheetId = sheet.getAttributeNS(relationshipNamespace, 'id');
    const sheetRelation = relations.find(item => item.getAttribute('Id') === sheetId && item.getAttribute('Type') === relationshipNamespace + '/worksheet');
    if (!sheetRelation || sheetRelation.getAttribute('TargetMode') === 'External') {
        throw new Error('The Forecast By SKU sheet could not be found in the workbook.');
    }
    const sourceSheet = parseXml(await readFile(input, resolveTarget(sheetRelation.getAttribute('Target'))));
    const sheetData = sourceSheet.getElementsByTagNameNS(mainNamespace, 'sheetData')[0];
    if (!sheetData) {
        throw new Error('The Forecast By SKU sheet contains no data.');
    }

    const outputDocument = document.implementation.createDocument(mainNamespace, 'worksheet', null);
    outputDocument.documentElement.appendChild(outputDocument.importNode(sheetData, true));

    const output = new JSZip();
    const extra = [];
    for (const name of ['sharedStrings', 'styles']) {
        const relation = relations.find(item => item.getAttribute('Type') === relationshipNamespace + '/' + name);
        if (relation) {
            if (relation.getAttribute('TargetMode') === 'External') {
                throw new Error('The workbook contains an invalid Excel data path.');
            }
            const source = input.file(resolveTarget(relation.getAttribute('Target')));
            if (!source) {
                throw new Error('The workbook is missing ' + name + '.');
            }
            output.file('xl/' + name + '.xml', await source.async('uint8array'));
            extra.push(name);
        }
    }
    output.file('xl/worksheets/sheet1.xml', new XMLSerializer().serializeToString(outputDocument));
    output.file('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="' + mainNamespace + '" xmlns:r="' + relationshipNamespace + '"><sheets><sheet name="Forecast By SKU" sheetId="1" r:id="rId1"/></sheets></workbook>');
    output.file('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="' + packageNamespace + '"><Relationship Id="rId1" Type="' + relationshipNamespace + '/worksheet" Target="worksheets/sheet1.xml"/>' + extra.map((name, index) => '<Relationship Id="rId' + (index + 2) + '" Type="' + relationshipNamespace + '/' + name + '" Target="' + name + '.xml"/>').join('') + '</Relationships>');
    output.file('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="' + packageNamespace + '"><Relationship Id="rId1" Type="' + relationshipNamespace + '/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    output.file('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="' + contentNamespace + '"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' + extra.map(name => '<Override PartName="/xl/' + name + '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.' + name + '+xml"/>').join('') + '</Types>');
    return output.generateAsync({ type: 'blob', compression: 'DEFLATE' });
}

const form = document.getElementById('demand-forecast-form');
const input = document.getElementById('forecast_file');
const message = document.getElementById('forecast-upload-message');
const button = document.getElementById('forecast-upload-button');

form.addEventListener('submit', async event => {
    event.preventDefault();
    message.textContent = '';
    button.disabled = true;
    button.textContent = 'Preparing forecast...';
    try {
        const blob = await extractForecast(input.files[0]);
        if (blob.size > 1536 * 1024) {
            throw new Error('The forecast sheet is too large to upload. Please reduce the workbook or ask an administrator to increase upload limits.');
        }
        const files = new DataTransfer();
        files.items.add(new File([blob], 'forecast.xlsx', { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }));
        input.files = files.files;
        form.submit();
        button.disabled = false;
        button.textContent = 'Convert and download';
    } catch (error) {
        message.textContent = 'Could not prepare the forecast: ' + error.message;
        button.disabled = false;
        button.textContent = 'Convert and download';
    }
});
