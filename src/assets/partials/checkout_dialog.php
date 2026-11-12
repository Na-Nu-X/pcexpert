<?php
    /**
     * Checkout Dialog (Shared 3 Step Ordering Wizard)
     *
     * Expects These Variables Before Including:
     *
     * @var array $checkout - Checkout Configuration (
     *     service              - Service Key (computer_building | computer_maintenance | computer_cleaning),
     *     title                - Service Title,
     *     icon                 - Font Awesome Icon Class,
     *     price                - Price In EUR,
     *     details_title        - Title Of The Third Step,
     *     details_description  - Description Of The Third Step,
     *     details_file         - Partial With The Service Specific Questions,
     *     message_placeholder  - Message Textarea Placeholder,
     *     message_required     - Whether The Message Is Required
     * )
     * @var array $checkout_prefill - Prefilled Customer Data (first_name, last_name, email_address, phone_number)
     * @var string $attachment_message - Default Attachment Report Message
    **/

    $service = $checkout["service"]; // Service Key (Used As An ID Prefix)
    $title_id = $service . "_checkout_title";
    $all_steps = ["Kontakt", "Doručenie", "Detaily"];
?>

<dialog class="checkout_modal" aria-labelledby="<?php echo $title_id; ?>" data-checkout>
    <div class="checkout">
        <!-- Header -->

        <div class="checkout_header">
            <div class="checkout_summary">
                <span class="checkout_icon" aria-hidden="true"><i class="<?php echo $checkout["icon"]; ?>"></i></span>

                <div class="checkout_summary_text">
                    <p class="checkout_label">Vaša objednávka</p>
                    <h2 class="checkout_title" id="<?php echo $title_id; ?>"><?php echo $checkout["title"]; ?></h2>
                </div>

                <p class="checkout_price"><?php echo $checkout["price"]; ?>&nbsp;€</p>

                <button class="checkout_close" type="button" title="Zavrieť" aria-label="Zavrieť" data-checkout-close><i class="fa-solid fa-xmark"></i></button> <!-- https://fontawesome.com/icons/xmark -->
            </div>

            <ol class="checkout_progress" aria-label="Priebeh objednávky">
                <?php foreach($all_steps as $index => $step_name): ?>
                    <li class="<?php echo $index === 0 ? "active" : ""; ?>" data-step-indicator>
                        <span class="dot"><?php echo $index + 1; ?></span>
                        <span class="name"><?php echo $step_name; ?></span>
                    </li>

                <?php endforeach; ?>
            </ol>
        </div>

        <!-- Body -->

        <div class="checkout_body" data-checkout-body>
            <!-- Step 1: Contact -->

            <fieldset class="checkout_step contact_section active" data-step>
                <legend class="visually_hidden">Kontaktné údaje</legend>

                <div class="checkout_step_heading">
                    <h3>Kontaktné údaje</h3>
                    <p>Aby sme vás mohli kontaktovať ohľadom vašej objednávky.</p>
                </div>

                <div class="field_grid">
                    <div class="field">
                        <label for="<?php echo $service; ?>_first_name">Meno</label>
                        <input id="<?php echo $service; ?>_first_name" type="text" name="first_name" placeholder="Ján" autocomplete="given-name" value="<?php echo htmlspecialchars($checkout_prefill["first_name"], ENT_QUOTES, "UTF-8"); ?>" required>
                    </div>

                    <div class="field">
                        <label for="<?php echo $service; ?>_last_name">Priezvisko</label>
                        <input id="<?php echo $service; ?>_last_name" type="text" name="last_name" placeholder="Novák" autocomplete="family-name" value="<?php echo htmlspecialchars($checkout_prefill["last_name"], ENT_QUOTES, "UTF-8"); ?>" required>
                    </div>

                    <div class="field full">
                        <label for="<?php echo $service; ?>_email_address">E-mail</label>
                        <input id="<?php echo $service; ?>_email_address" type="email" name="email_address" placeholder="jan.novak@email.sk" autocomplete="email" value="<?php echo htmlspecialchars($checkout_prefill["email_address"], ENT_QUOTES, "UTF-8"); ?>" required>
                    </div>

                    <div class="field full">
                        <label for="<?php echo $service; ?>_phone_number">Telefónne číslo</label>
                        <input id="<?php echo $service; ?>_phone_number" class="phone_number" type="tel" name="phone_number" placeholder="+421 900 000 000" autocomplete="tel" value="<?php echo htmlspecialchars($checkout_prefill["phone_number"], ENT_QUOTES, "UTF-8"); ?>" required>
                    </div>
                </div>
            </fieldset>

            <!-- Step 2: Delivery -->

            <fieldset class="checkout_step delivery_section" data-step>
                <legend class="visually_hidden">Dodacie údaje</legend>

                <div class="checkout_step_heading">
                    <h3>Dodacie údaje</h3>
                    <p>Kam vám máme zariadenie po dokončení doručiť?</p>
                </div>

                <div class="field_grid">
                    <div class="field full">
                        <label for="<?php echo $service; ?>_address">Ulica a číslo</label>
                        <input id="<?php echo $service; ?>_address" type="text" name="address" placeholder="Hlavná 12" autocomplete="address-line1" required>
                    </div>

                    <div class="field">
                        <label for="<?php echo $service; ?>_city">Mesto</label>
                        <input id="<?php echo $service; ?>_city" type="text" name="city" placeholder="Bratislava" autocomplete="address-level2" required>
                    </div>

                    <div class="field">
                        <label for="<?php echo $service; ?>_postal_code">PSČ</label>
                        <input id="<?php echo $service; ?>_postal_code" type="text" name="postal_code" placeholder="821 08" autocomplete="postal-code" inputmode="numeric" required>
                    </div>
                </div>

                <div class="shipping_methods" role="radiogroup" aria-label="Spôsob doručenia">
                    <p class="field_label">Spôsob doručenia</p>

                    <div class="shipping_method packeta" data-shipping-method>
                        <label class="option_card">
                            <input class="shipping_method_input" type="radio" name="shipping_method" value="packeta" required>
                            <span class="option_card_icon" aria-hidden="true"><i class="fa-solid fa-box"></i></span> <!-- https://fontawesome.com/icons/box -->
                            <span class="option_card_text">
                                <span class="option_card_title">Packeta</span>
                                <span class="option_card_description">Z-Box alebo výdajné miesto</span>
                            </span>
                        </label>

                        <div class="shipping_details" hidden data-shipping-details>
                            <div class="packeta_selection">
                                <p class="selected_packeta_branch_name" data-packeta-label>Ešte nebolo vybrané žiadne miesto.</p>
                                <button class="btn btn_secondary btn_sm" type="button" data-packeta-open><i class="fa-solid fa-location-dot"></i> Vybrať miesto</button> <!-- https://fontawesome.com/icons/location-dot -->
                            </div>

                            <div class="packeta_widget_container" hidden data-packeta-widget></div>

                            <input type="hidden" name="packeta_branch_id" data-packeta-id data-required>
                            <input type="hidden" name="packeta_branch_name" data-packeta-name data-required>
                        </div>
                    </div>
                </div>
            </fieldset>

            <!-- Step 3: Service Details -->

            <fieldset class="checkout_step details_section" data-step>
                <legend class="visually_hidden"><?php echo $checkout["details_title"]; ?></legend>

                <div class="checkout_step_heading">
                    <h3><?php echo $checkout["details_title"]; ?></h3>
                    <p><?php echo $checkout["details_description"]; ?></p>
                </div>

                <div class="questions">
                    <?php require __DIR__ . "/" . $checkout["details_file"]; ?>
                </div>

                <div class="field">
                    <label for="<?php echo $service; ?>_message"><?php echo $checkout["message_required"] ? "Popis" : "Poznámka (nepovinné)"; ?></label>
                    <textarea id="<?php echo $service; ?>_message" name="message" placeholder="<?php echo $checkout["message_placeholder"]; ?>" maxlength="250" rows="4" <?php echo $checkout["message_required"] ? "required" : ""; ?>></textarea>
                </div>

                <div class="attachment_container">
                    <label class="attachment" for="<?php echo $service; ?>_attachment">
                        <i class="fa-solid fa-paperclip" aria-hidden="true"></i> <!-- https://fontawesome.com/icons/paperclip -->
                        <span>Priložiť súbory <small>(max. 5 súborov, 25 MB)</small></span>
                    </label>

                    <p class="attachment_report"><?php echo htmlspecialchars($attachment_message, ENT_QUOTES, "UTF-8"); ?></p>

                    <input class="attachment_input" id="<?php echo $service; ?>_attachment" type="file" name="<?php echo $service; ?>_attachment[]" accept=".jpg, .jpeg, .png, .gif, .webp, .bmp, .tiff, .tif, .mp4, .mov, .avi, .mkv, .webm, .hevc, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx, .txt, .rtf, .odt, .ods, .odp" multiple>
                </div>
            </fieldset>
        </div>

        <!-- Footer -->

        <div class="checkout_footer">
            <button class="btn btn_ghost" type="button" data-checkout-previous hidden><i class="fa-solid fa-arrow-left"></i> Späť</button> <!-- https://fontawesome.com/icons/arrow-left -->

            <p class="checkout_step_counter">Krok <strong data-step-current>1</strong> z <?php echo count($all_steps); ?></p>

            <button class="btn" type="button" data-checkout-next disabled>Ďalej <i class="fa-solid fa-arrow-right"></i></button> <!-- https://fontawesome.com/icons/arrow-right -->
            <button class="btn" name="pay" type="submit" data-checkout-pay disabled hidden><i class="fa-solid fa-lock"></i> Zaplatiť <?php echo $checkout["price"]; ?>&nbsp;€</button> <!-- https://fontawesome.com/icons/lock -->
        </div>
    </div>
</dialog>
