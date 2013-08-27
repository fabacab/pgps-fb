<form id="pgps-search" action="<?php $_SERVER['PHP_SELF']?>">
    <label>
        Lookup <input list="friends-list" name="show_user" placeholder="friend's name" required="required" /> pronouns.
        <datalist id="friends-list">
            <select><!-- For non-HTML5 fallback. -->
                <?php foreach ($friends['data'] as $friend) : ?>
                <option value="<?php print he($friend['name']);?>"><?php print he($friend['name']);?></option>
                <?php endforeach;?>
            </select>
        </datalist>
    </label>
    <input type="submit" name="search" value="Search" />
</form>
