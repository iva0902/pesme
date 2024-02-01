<?php
    include './komponente/header.php';
?>
<div class="row">
    <div class="col-11">
        <h1 class="text-center">Pemse</h1>
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
            <th>Naziv</th>
            <th>Trajanje</th>
            <th>Pevac</th>
            <th>Zanr</th>
            <th>Izmeni</th>
            <th>Obrisi</th>
        </tr>
    </thead>
    <tbody id='pesme'>

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
                        <label for="naziv" class="col-form-label">Naziv</label>
                        <input required type="text" class="form-control" id="naziv">
                    </div>
                    <div class="form-group">
                        <label for="trajanje" class="col-form-label">Trajanje</label>
                        <input required type="number" class="form-control" id="trajanje">
                    </div>
                    <div class="form-group">
                        <label for="zanr" class="col-form-label">Zanr</label>
                        <select required type="number" class="form-control" id="zanr"></select>
                    </div>
                    <div class="form-group">
                        <label for="pevac" class="col-form-label">Pevac</label>
                        <select required type="number" class="form-control" id="pevac"></select>
                    </div>
                    <button type="submit" class="btn btn-primary form-control">Sacuvaj</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        let id = 0;
        let pesme = [];
        $(document).ready(function () {
            ucitaj();
            $('#pretraga').change(iscrtajTabelu);
            ucitajOpcije('zanr', 'zanr', e => e.naziv);
            ucitajOpcije('pevac', 'pevac', e => e.ime + ' ' + e.prezime);
            $('#exampleModal').on('show.bs.modal', function (e) {
                let dugme = $(e.relatedTarget);
                const odabraniId = dugme.data('id');
                const pesma = pesme.find(e => e.id == odabraniId);
                if (!pesma) {
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
                    naziv: $('#naziv').val(),
                    trajanje: $("#trajanje").val(),
                    zanr: $("#zanr").val(),
                    pevac: $("#pevac").val(),
                }
                if (id === 0) {
                    $.post('./api/pesma/kreiraj.php', telo, function (res) {
                        res = JSON.parse(res);
                        if (!res.status) {
                            alert(res.error)
                            return;
                        }
                        pesme.push(res.data);
                        iscrtajTabelu();
                    })
                } else {
                    $.post('./api/pesma/izmeni.php?id=' + id, telo, function (res) {
                        res = JSON.parse(res);
                        if (!res.status) {
                            alert(res.error)
                            return;
                        }
                        const index = pesme.findIndex(e => e.id == id);
                        pesme[index] = res.data;
                        iscrtajTabelu();
                    })
                }
            })
        })
        function ucitaj() {
            $.getJSON('./api/pesma/ucitaj.php', function (res) {
                if (!res.status) {
                    alert(res.error)
                    return;
                }
                pesme = res.data;
                iscrtajTabelu();
            })
        }
        function iscrtajTabelu() {
            let pretraga = $('#pretraga').val();
            $('#pesme').html('');
            for (let pesma of pesme) {
                if (pesma.naziv.toLocaleLowerCase().includes(pretraga.toLocaleLowerCase())) {
                    $('#pesme').append(`
                    <tr>
                        <td>${pesma.id}</td>
                        <td>${pesma.naziv}</td>
                        <td>${pesma.trajanje}</td>
                        <td>${pesma.pevac.ime + ' ' + pesma.pevac.prezime}</td>
                        <td>${pesma.zanr.naziv}</td>
                        <td>
                            <button data-id=${pesma.id} 
                                data-toggle='modal'
                                data-target='#exampleModal'
                                class="form-control btn" >Izmeni</button>
                        </td>
                        <td>
                            <button 
                                onClick="obrisi(${pesma.id})"
                                class="form-control btn btn-danger" >Obrisi</button>
                        </td>
                    </tr>
                `)
                }
            }
        }
        function obrisi(selId) {
            $.post('./api/pesma/obrisi.php?id=' + selId, function (res) {
                res = JSON.parse(res);
                if (!res.status) {
                    alert(res.data)
                } else {
                    pesme = pesme.filter(function (e) {
                        return e.id != selId;
                    })
                    id = 0;
                    iscrtajTabelu();
                }
            })
        }
        function ucitajOpcije(klasa, htmlId, fSadrzaj) {
            $.getJSON(`./api/${klasa}/ucitaj.php`, function (res) {
                if (!res.status) {
                    alert(res.error);
                    return;
                }
                for (let opcija of res.data) {
                    $('#' + htmlId).append(`
                        <option value='${opcija.id}'>
                            ${fSadrzaj(opcija)}
                        </option>
                    `)
                }
            })
        }
    </script>
    <?php
    include './komponente/footer.php';
?>