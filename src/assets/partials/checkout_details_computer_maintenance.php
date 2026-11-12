<!-- Computer Maintenance Questions (Included By checkout_dialog.php) -->

<div class="question">
    <p class="question_title">Typ zariadenia</p>

    <div class="chips" role="radiogroup" aria-label="Typ zariadenia">
        <label class="chip"><input type="radio" name="device_type" value="desktop_pc" required><i class="fa-solid fa-desktop" aria-hidden="true"></i><span>Stolný počítač</span></label> <!-- https://fontawesome.com/icons/desktop -->
        <label class="chip"><input type="radio" name="device_type" value="notebook" required><i class="fa-solid fa-laptop" aria-hidden="true"></i><span>Notebook</span></label> <!-- https://fontawesome.com/icons/laptop -->
    </div>
</div>

<div class="question">
    <p class="question_title">Aké problémy evidujete?</p>
    <p class="question_description">Označte všetky, ktoré ste spozorovali.</p>

    <div class="chips" role="group" aria-label="Problémy" data-required-group>
        <label class="chip"><input type="checkbox" name="problems[]" value="overheating"><span>Prehrievanie</span></label>
        <label class="chip"><input type="checkbox" name="problems[]" value="noise"><span>Nadmerný hluk</span></label>
        <label class="chip"><input type="checkbox" name="problems[]" value="shutdowns"><span>Samovoľné vypínanie</span></label>
    </div>
</div>

<div class="question">
    <p class="question_title">Softvérové služby</p>
    <p class="question_description">Vyberte aspoň jednu službu, o ktorú máte záujem.</p>

    <div class="chips" role="group" aria-label="Softvérové služby" data-required-group>
        <label class="chip"><input type="checkbox" name="software_services[]" value="optimization"><span>Optimalizácia</span></label>
        <label class="chip"><input type="checkbox" name="software_services[]" value="reinstall"><span>Reinštalácia systému</span></label>
    </div>
</div>
