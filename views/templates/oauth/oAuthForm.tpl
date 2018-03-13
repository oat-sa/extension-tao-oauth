<div class="basic-auth-form">
    <div class="login-container">
        <label for="client-key" class="form_desc"><?=__('Client key')?></label>
        <input type="text" id="client-key" name="<?= \tao_helpers_Uri::encode('http://www.tao.lu/Ontologies/TAO.rdf#OauthKey') ?>"
               class="credential-field"
               value="<?=get_data('http://www.tao.lu/Ontologies/TAO.rdf#OauthKey')?>"/>
    </div>
    <div>
        <label for="client-secret"><?=__('Client secret')?></label>
        <input type="text" id="client-secret" name="<?= \tao_helpers_Uri::encode('http://www.tao.lu/Ontologies/TAO.rdf#OauthSecret') ?>"
               class="credential-field"
               value="<?=get_data('http://www.tao.lu/Ontologies/TAO.rdf#OauthSecret')?>"/>
    </div>
    <div>
        <label for="token-url" ><?=__('Token url')?></label>
        <input type="text" id="token-url" name="<?= \tao_helpers_Uri::encode('http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl') ?>"
               class="credential-field"
               value="<?=get_data('http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl')?>"/>
    </div>
</div>