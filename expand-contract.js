<script>
function showhide(id){
    var x = document.getElementById("spifferjobs" + id);
    if (x.style.height != "100%") {
        x.style.height = "100%";
    }
    else {
        x.style.height = "0";
    }
    var y = document.getElementById("caret" + id);
    if (y.style.transform != 'rotate(180deg)') {
        y.style.transform = 'rotate(180deg)';
    }
    else {
        y.style.transform = 'rotate(0deg)';
    }
}
function showhideservice(id) {
    var x = document.getElementById("services" + id);
    if (x.style.height != "100%") {
        x.style.height = "100%";
    }
    else {
        x.style.height = "0";
    }
    var y = document.getElementById("jobcaret" + id);
    if (y.style.transform != 'rotate(180deg)') {
        y.style.transform = 'rotate(180deg)';
    }
    else {
        y.style.transform = 'rotate(0deg)';
    }
}
    
function showhideinvoice(id) {
    var x = document.getElementById("invoices" + id);
    if (x.style.height != "100%") {
        x.style.height = "100%";
    }
    else {
        x.style.height = "0";
    }
    var y = document.getElementById("invoicecaret" + id);
    if (y.style.transform != 'rotate(180deg)') {
        y.style.transform = 'rotate(180deg)';
    }
    else {
        y.style.transform = 'rotate(0deg)';
    }
}
</script>