<?php
$this->template()->addJs('\updates/github.js');
$this->template()->addMeta('keywords', 'обновления, github');
$this->template()->setTitle('Github');

?>
<p>Загрузка обновлений из GitHub</p>


<label>Ссылка на репозиторий</label>
<form action="#" class="github_link_form">
<div class="input-group">
    <input type="text" class="form-control github_link">
    <span class="input-group-btn">
        <button class="btn btn-default github_link_submit" type="button">Проверить!</button>
   </span>
</div>
</form>
<div class="github_content">

</div>