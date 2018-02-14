<div class="basic-auth-form">
    <div class="login-container">
        <label for="clientId" class="form_desc"><?=__('clientId')?></label>
        <input type="text" id="clientId" name="<?= \tao_helpers_Uri::encode('http://www.taotesting.com/ontologies/taooauth.rdf#ClientId') ?>"
               class="credential-field"
               value="<?=get_data('http://www.taotesting.com/ontologies/taooauth.rdf#ClientId')?>"/>
    </div>
    <div>
        <label for="clientSecret"><?=__('clientSecret')?></label>
        <input type="text" id="clientSecret" name="<?= \tao_helpers_Uri::encode('http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret') ?>"
               class="credential-field"
               value="<?=get_data('http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret')?>"/>
    </div>
    <div>
        <label for="tokenUrl"><?=__('tokenUrl')?></label>
        <input type="text" id="tokenUrl" name="<?= \tao_helpers_Uri::encode('http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl') ?>"
               class="credential-field"
               value="<?=get_data('http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl')?>"/>
    </div>
</div>