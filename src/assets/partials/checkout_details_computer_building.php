<!-- Computer Building Questions (Included By checkout_dialog.php) -->

<div class="question range_field" data-range>
    <div class="range_header">
        <label class="question_title" for="computer_building_max_price">Maximálny rozpočet</label>
        <output class="range_output" for="computer_building_max_price" data-range-output>1000 €</output>
    </div>

    <p class="question_description">Rozpočet na samotné komponenty (bez ceny za prácu).</p>

    <input id="computer_building_max_price" type="range" name="max_price" value="1000" min="300" max="3000" step="50" data-range-input data-unit="€">
</div>

<div class="question">
    <p class="question_title">Procesor</p>
    <p class="question_description">Preferovaná značka procesora.</p>

    <div class="chips" role="radiogroup" aria-label="Procesor">
        <label class="chip"><input type="radio" name="cpu" value="amd" required><span>AMD</span></label>
        <label class="chip"><input type="radio" name="cpu" value="intel" required><span>Intel</span></label>
    </div>
</div>

<div class="question">
    <p class="question_title">Grafická karta</p>
    <p class="question_description">Preferovaná značka grafickej karty.</p>

    <div class="chips" role="radiogroup" aria-label="Grafická karta">
        <label class="chip"><input type="radio" name="gpu" value="nvidia" required><span>NVIDIA</span></label>
        <label class="chip"><input type="radio" name="gpu" value="amd" required><span>AMD</span></label>
    </div>
</div>

<div class="question range_field" data-range>
    <div class="range_header">
        <label class="question_title" for="computer_building_ram">Operačná pamäť (RAM)</label>
        <output class="range_output" for="computer_building_ram" data-range-output>16 GB</output>
    </div>

    <input id="computer_building_ram" type="range" min="0" max="5" step="1" value="1" data-range-input data-values="8,16,32,64,128,256" data-unit="GB">
    <input type="hidden" name="ram" value="16" data-range-value>
</div>

<div class="question range_field" data-range>
    <div class="range_header">
        <label class="question_title" for="computer_building_space">Kapacita disku</label>
        <output class="range_output" for="computer_building_space" data-range-output>1024 GB</output>
    </div>

    <input id="computer_building_space" type="range" min="0" max="5" step="1" value="3" data-range-input data-values="128,256,512,1024,2048,4096" data-unit="GB">
    <input type="hidden" name="space" value="1024" data-range-value>
</div>

<div class="question">
    <p class="question_title">Typ disku</p>
    <p class="question_description">Odporúčame rýchle M.2 NVMe SSD.</p>

    <div class="chips" role="radiogroup" aria-label="Typ disku">
        <label class="chip"><input type="radio" name="disk_type" value="m2" required><span>M.2 SSD</span></label>
        <label class="chip"><input type="radio" name="disk_type" value="ssd" required><span>SSD</span></label>
        <label class="chip"><input type="radio" name="disk_type" value="hdd" required><span>HDD</span></label>
    </div>
</div>
