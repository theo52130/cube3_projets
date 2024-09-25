<div id="pageFactures">

    <h3>Factures</h3>

    <table border="1">
        <tr>
            <th>Facture ID</th>
            <th>Date</th>
            <th>Total</th>
            <th>État</th>
            <th>Produits</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($factureData as $facture): ?>
            <tr>
                <td><?php echo $facture['facture_id']; ?></td>
                <td><?php echo $facture['date_creation']; ?></td>
                <td><?php echo $facture['total']; ?></td>
                <td><?php echo $facture['etat']; ?></td>
                <td>
                    <?php foreach ($facture['produits'] as $produit): ?>
                        <p><?php echo $produit['description'] . ' - ' . $produit['prix_unitaire'] . ' - ' . $produit['quantite']; ?></p>
                    <?php endforeach; ?>
                </td>
                <td>
                    <form method="post">
                        <button name="download_pdf" type="submit">Download PDF</button>
                        <button name="download_csv" type="submit">Download CSV</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</div>