import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';

let passed = 0;
const check = (name, action) => { action(); passed++; console.log('PASS ' + name); };

class Form {
    inputs = [];
    querySelectorAll(selector) {
        const match = /^input(?:\[type="([^"]+)"\])?\[name="([^"]+)"\]$/.exec(selector);
        assert.ok(match, 'fixture supports the real named-input selector: ' + selector);
        return this.inputs.filter(input => input.name === match[2] && (!match[1] || input.type === match[1]));
    }
    add(name, value = '', options = {}) {
        const input = {name, value, type: 'text', checked: false, disabled: false, handlers: new Map(), form: this, ...options};
        input.addEventListener = (type, fn) => {
            if (!input.handlers.has(type)) input.handlers.set(type, []);
            input.handlers.get(type).push(fn);
        };
        this.inputs.push(input);
        return input;
    }
    choose(input, inline) {
        for (const other of this.inputs) if (other.type === 'radio' && other.name === input.name) other.checked = other === input;
        inline?.();
        for (const handler of input.handlers.get('change') || []) handler({target: input});
    }
}
const radios = (form, name, value, ids = ['input3', 'input4']) => ['Y', 'N'].map((item, index) => form.add(name, item, {type: 'radio', checked: item === value, id: ids[index]}));
const dates = (form, start, end, disabled = false) => [form.add(start, '2026-11-10', {disabled}), form.add(end, '2026-11-11', {disabled})];
const disabled = (inputs, expected) => inputs.forEach(input => assert.equal(input.disabled, expected));

const banner = new Form();
const bannerPeriod = radios(banner, 'b_none_limit', 'Y');
const bannerDates = dates(banner, 'b_sdate', 'b_edate');
const bannerView = radios(banner, 'b_none_view', 'N', ['b_none_view_input_1', 'b_none_view_input_2']);
const bannerViewDates = dates(banner, 'b_sdate_view', 'b_edate_view', true);
const linkNone = banner.add('b_target', '_none', {type: 'radio', id: 'input5', checked: true});
const linkSelf = banner.add('b_target', '_self', {type: 'radio', id: 'input6'});
const linkBlank = banner.add('b_target', '_blank', {type: 'radio', id: 'input7'});

const popup = new Form();
const popupPeriod = radios(popup, 'p_none_limit', 'Y');
const popupDates = dates(popup, 'p_sdate', 'p_edate');
const popupLink = popup.add('p_target', '_self', {type: 'radio', id: 'input6', checked: true});

const works = new Form();
const worksDates = dates(works, 'start_date', 'end_date');
const genre1 = works.add('genre', '1', {type: 'radio', id: 'input9', checked: true});
const genre2 = works.add('genre', '2', {type: 'radio', id: 'input10'});
const rental = new Form();
const rentalDates = dates(rental, 'r_sdate', 'r_edate');
const unrelatedDate = rental.add('w_sdate', '2026-11-10', {disabled: true});
const secondBanner = new Form();
const secondPeriod = radios(secondBanner, 'b_none_limit', 'N');
const secondDates = dates(secondBanner, 'b_sdate', 'b_edate', true);
const unselected = new Form();
radios(unselected, 'p_none_limit', '');
const unselectedDates = dates(unselected, 'p_sdate', 'p_edate', true);
const invalid = new Form();
invalid.add('p_none_limit', 'unexpected', {type: 'radio', checked: true});
const invalidDates = dates(invalid, 'p_sdate', 'p_edate', true);

const forms = [banner, popup, works, rental, secondBanner, unselected, invalid];
const document = {querySelectorAll: selector => forms.flatMap(form => form.querySelectorAll(selector))};
const initializedDatepickers = [];
function $(selector) {
    return {
        ready(callback) { callback(); return this; },
        selectmenu() { return this; },
        datepicker(options) { initializedDatepickers.push({selector, options}); return this; },
        submit() { return this; }
    };
}
vm.runInNewContext(fs.readFileSync(new URL('../admin/resource/js/form.js', import.meta.url), 'utf8'), {$, document, console});

check('banner initial unlimited and scheduled groups synchronize separately', () => { disabled(bannerDates, true); disabled(bannerViewDates, false); });
check('second form with same names synchronizes its own state', () => disabled(secondDates, false));
check('scheduled banner performance dates enable on change', () => { banner.choose(bannerPeriod[1]); disabled(bannerDates, false); });
check('banner display unlimited disables only display dates', () => { banner.choose(bannerView[0]); disabled(bannerViewDates, true); disabled(bannerDates, false); });
check('same-window link radio sharing input6 cannot re-enable display dates', () => { banner.choose(linkSelf); disabled(bannerViewDates, true); disabled(bannerDates, false); assert.equal(linkSelf.handlers.size, 0); });
check('new-window and no-link radios do not change dates', () => { banner.choose(linkBlank); banner.choose(linkNone); disabled(bannerViewDates, true); disabled(bannerDates, false); });
check('banner display schedule enables both date inputs', () => { banner.choose(bannerView[1]); disabled(bannerViewDates, false); });
check('banner schedule to unlimited preserves inline doAttrChange compatibility', () => { banner.choose(bannerView[0], () => bannerViewDates.forEach(input => { input.disabled = true; })); disabled(bannerViewDates, true); });
check('different form period controls stay independent', () => { banner.choose(bannerPeriod[0]); disabled(bannerDates, true); disabled(secondDates, false); secondBanner.choose(secondPeriod[0]); disabled(secondDates, true); disabled(bannerViewDates, true); });
check('popup initial unlimited date fields disabled', () => disabled(popupDates, true));
check('popup scheduled then unlimited round trip', () => { popup.choose(popupPeriod[1]); disabled(popupDates, false); popup.choose(popupPeriod[0]); disabled(popupDates, true); });
check('popup link target does not affect optional period state', () => { popup.choose(popupLink); disabled(popupDates, true); assert.equal(popupLink.handlers.size, 0); });
check('required works dates stay enabled when genre uses input9 and input10', () => { works.choose(genre2); works.choose(genre1); disabled(worksDates, false); });
check('rental required dates and unrelated disabled input are preserved', () => { disabled(rentalDates, false); assert.equal(unrelatedDate.disabled, true); });
check('missing selection and invalid flag do not mutate existing date state', () => { disabled(unselectedDates, true); disabled(invalidDates, true); });
check('date toggles never discard entered values', () => { for (const pair of [bannerDates, bannerViewDates, popupDates]) assert.deepEqual(pair.map(input => input.value), ['2026-11-10', '2026-11-11']); });
check('shared datepicker setup remains initialized for required and optional inputs', () => { for (const name of ['#b_sdate_view', '#p_sdate', '#r_sdate', '#start_date']) assert.ok(initializedDatepickers.some(item => item.selector === name && item.options.dateFormat === 'yy-mm-dd')); });
console.log(`${passed} PASS / 0 FAIL`);
