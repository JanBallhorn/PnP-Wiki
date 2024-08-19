function deleteAlert(subject){
    let alert = prompt('Bist du sicher, dass du "' + subject + '" löschen möchtest? \r\nDann gib hier "löschen" ein!');
    let path = $(location).attr('pathname');
    let pathArray = path.slice(1).split("/");
    let newPath = '';
    if(pathArray[0] === 'project'){
        newPath = "/project/delete";
    }
    if(pathArray[0] === 'category'){
        newPath = "/category/delete";
    }
    if(alert === "löschen"){
        location.href = newPath + "?name=" + subject;
    }
}