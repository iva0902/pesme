<?php
    include './komponente/header.php';
?>

<div class="row">
    <div class="col-11">
        <h1 class="text-center">Pevaci</h1>
    </div>
    <div class="col-1">
        <button data-toggle='modal' data-target='#exampleModal' class="form-control btn btn-primary">Kreiraj</button>
    </div>
</div>
<input id='pretraga' placeholder="Pretrazi..." class="form-control" type="text">
<table class="table mt-2">
    <thead>
        <tr>
            <th>ID</th>
            <th>Ime</th>
            <th>Prezime</th>
            <th>Godina rodjenja</th>
            <th>Izmeni</th>
            <th>Obrisi</th>
        </tr>
    </thead>
    <tbody id='pevaci'>

    </tbody>
</table>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Forma pevac</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form id="forma">
                    <div class="form-group">
                        <label for="ime" class="col-form-label">Ime</label>
                        <input required type="text" class="form-control" id="ime">
                    </div>
                    <div class="form-group">
                        <label for="prezime" class="col-form-label">Prezime</label>
                        <input required type="text" class="form-control" id="prezime">
                    </div>
                    <div class="form-group">
                        <label for="godinaRodjenja" class="col-form-label">Godina rodjenja</label>
                        <input required type="number" class="form-control" id="godinaRodjenja">
                    </div>
                    <button type="submit" class="btn btn-primary form-control">Sacuvaj</button>
                </form>
            </div>
        </div>
    </div>

</div>
<script>
    let id = 0;
    let pevaci = [];
    $(document).ready(function () {
        ucitaj();
        $('#pretraga').change(iscrtajTabelu);
        $('#exampleModal').on('show.bs.modal', function (e) {
            let dugme = $(e.relatedTarget);
            const odabraniId = dugme.data('id');
            const pevac = pevaci.find(e => e.id == odabraniId);
            if (!pevac) {
                id = 0;
                $('#ime').val('');
                $('#prezime').val('');
                $('#godinaRodjenja').val('');
            } else {
                id = odabraniId;
                $('#ime').val(pevac.ime);
                $('#prezime').val(pevac.prezime);
                $('#godinaRodjenja').val(pevac.godinaRodjenja);
            }
        })
        $("#forma").submit(function (e) {
            e.preventDefault();
            let telo = {
                ime: $('#ime').val(),
                prezime: $("#prezime").val(),
                godinaRodjenja: $("#godinaRodjenja").val()
            }
            if (id === 0) {
                $.post('./api/pevac/kreiraj.php', telo, function (res) {
                    res = JSON.parse(res);
                    if (!res.status) {
                        alert(res.error)
                        return;
                    }
                    pevaci.push(res.data);
                    iscrtajTabelu();
                })
            } else {
                $.post('./api/pevac/izmeni.php?id=' + id, telo, function (res) {
                    res = JSON.parse(res);
                    if (!res.status) {
                        alert(res.error)
                        return;
                    }
                    const index = pevaci.findIndex(e => e.id == id);
                    pevaci[index] = res.data;
                    iscrtajTabelu();
                })
            }
        })
    })
    function ucitaj() {
        $.getJSON('./api/pevac/ucitaj.php', function (res) {
            if (!res.status) {
                alert(res.error)
                return;
            }
            pevaci = res.data;
            iscrtajTabelu();
        })
    }
    function iscrtajTabelu() {
        let pretraga = $('#pretraga').val();
        $('#pevaci').html('');
        for (let pevac of pevaci) {
            if ((pevac.ime + ' ' + pevac.prezime).toLocaleLowerCase().includes(pretraga.toLocaleLowerCase())) {
                $('#pevaci').append(`
                    <tr>
                        <td>${pevac.id}</td>
                        <td>${pevac.ime}</td>
                        <td>${pevac.prezime}</td>
                        <td>${pevac.godinaRodjenja}</td>
                        <td>
                            <button data-id=${pevac.id} 
                                data-toggle='modal'
                                data-target='#exampleModal'
                                class="form-control btn" >Izmeni</button>
                        </td>
                        <td>
                            <button 
                                onClick="obrisi(${pevac.id})"
                                class="form-control btn btn-danger" >Obrisi</button>
                        </td>
                    </tr>
                `)
            }
        }
    }
    function obrisi(selId) {
        $.post('./api/pevac/obrisi.php?id=' + selId, function (res) {
            res = JSON.parse(res);
            if (!res.status) {
                alert(res.data)
            } else {
                pevaci = pevaci.filter(function (e) {
                    return e.id != selId;
                })
                id = 0;
                iscrtajTabelu();
            }
        })
    }
</script>
<?php
    include './komponente/footer.php';
?>