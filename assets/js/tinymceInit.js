tinymce.init({
    selector: 'textarea.tinymce',
    license_key: 'gpl',
    plugins: [ 'link','lists','table' ],
    toolbar: [
        { name: 'history', items: [ 'undo', 'redo' ] },
        { name: 'styles', items: [ 'styles' ] },
        { name: 'formatting', items: [ 'bold', 'italic','underline' ] },
        { name: 'alignment', items: [ 'alignleft', 'aligncenter', 'alignright', 'alignjustify' ] },
        { name: 'indentation', items: [ 'outdent', 'indent' ] },
        { name: 'lists', items: [ 'numlist', 'bullist' ] },
        { name: 'links', items: [ 'link', 'unlink' ] }
    ],
    language: 'de',
    promotion: false,
});