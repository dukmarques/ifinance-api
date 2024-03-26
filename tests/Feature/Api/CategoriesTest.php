<?php

it('has api/categories page', function () {
    $response = $this->get('/api/categories');

    $response->assertStatus(200);
});
