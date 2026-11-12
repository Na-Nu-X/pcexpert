<!-- Computer Cleaning Questions (Included By checkout_dialog.php) -->

<div class="question">
    <p class="question_title">Typ zariadenia</p>

    <div class="chips" role="radiogroup" aria-label="Typ zariadenia">
        <label class="chip"><input type="radio" name="device_type" value="desktop_pc" required><i class="fa-solid fa-desktop" aria-hidden="true"></i><span>Stolný počítač</span></label> <!-- https://fontawesome.com/icons/desktop -->
        <label class="chip"><input type="radio" name="device_type" value="notebook" required><i class="fa-solid fa-laptop" aria-hidden="true"></i><span>Notebook</span></label> <!-- https://fontawesome.com/icons/laptop -->
    </div>
</div>

<div class="question">
    <p class="question_title">Kedy bolo zariadenie naposledy čistené?</p>

    <div class="chips" role="radiogroup" aria-label="Posledné čistenie">
        <label class="chip"><input type="radio" name="last_cleaning" value="less_than_year" required><span>Pred menej ako rokom</span></label>
        <label class="chip"><input type="radio" name="last_cleaning" value="two_years_ago" required><span>Pred 1 až 2 rokmi</span></label>
        <label class="chip"><input type="radio" name="last_cleaning" value="never" required><span>Viac ako 2 roky / nikdy</span></label>
    </div>
</div>

<div class="question">
    <p class="question_title">Výmena teplovodivej pasty</p>
    <p class="question_description">Odporúčame, ak zariadenie dlhšie nebolo servisované.</p>

    <div class="chips" role="radiogroup" aria-label="Výmena teplovodivej pasty">
        <label class="chip"><input type="radio" name="thermal_paste_re-apply" value="yes" required><span>Áno</span></label>
        <label class="chip"><input type="radio" name="thermal_paste_re-apply" value="no" required><span>Nie</span></label>
    </div>
</div>
