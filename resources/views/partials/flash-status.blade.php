@php
    $flashMessages = [
        'requete_creee' => ['class' => 'alert-success', 'text' => 'Requête créée. Elle est visible pour votre entreprise et le support dans l’application.'],
        'media_ajoute' => ['class' => 'alert-success', 'text' => 'Fichier ajouté.'],
        'media_supprime' => ['class' => 'alert-warn', 'text' => 'Fichier supprimé.'],
        'planification_creee' => ['class' => 'alert-success', 'text' => 'Planification enregistrée. Le client et le technicien la voient sur la fiche et dans leurs listes.'],
        'planification_confirmee' => ['class' => 'alert-success', 'text' => 'Planification confirmée.'],
        'planification_annulee' => ['class' => 'alert-warn', 'text' => 'Planification annulée.'],
        'validation_client_arrivee' => ['class' => 'alert-success', 'text' => 'Arrivée du technicien enregistrée.'],
        'validation_client_intervention_en_cours' => ['class' => 'alert-success', 'text' => 'Intervention en cours confirmée par le client.'],
        'validation_client_fin' => ['class' => 'alert-success', 'text' => 'Fin d’intervention validée côté client.'],
        'validation_technicien_fin' => ['class' => 'alert-success', 'text' => 'Fin d’intervention confirmée par le technicien.'],
        'validation_deja_enregistree' => ['class' => 'alert-info', 'text' => 'Cette étape était déjà enregistrée.'],
        'intervention_creee' => ['class' => 'alert-success', 'text' => 'Intervention enregistrée.'],
        'intervention_mise_a_jour' => ['class' => 'alert-success', 'text' => 'Intervention mise à jour.'],
        'intervention_deja_existante' => ['class' => 'alert-warn', 'text' => 'Une intervention existe déjà pour cette requête : utilisez la mise à jour.'],
        'intervention_absente' => ['class' => 'alert-warn', 'text' => 'Aucune intervention à mettre à jour : créez-la d’abord.'],
        'recu_pdf_genere' => ['class' => 'alert-success', 'text' => 'Reçu PDF généré et enregistré.'],
        'recu_pdf_manquant' => ['class' => 'alert-warn', 'text' => 'Fichier PDF introuvable : générez le reçu d’abord.'],
        'client_cree' => ['class' => 'alert-success', 'text' => 'Entreprise créée.'],
        'client_mis_a_jour' => ['class' => 'alert-success', 'text' => 'Fiche mise à jour.'],
        'client_supprime' => ['class' => 'alert-success', 'text' => 'L’entreprise a été supprimée.'],
        'utilisateur_cree' => ['class' => 'alert-success', 'text' => 'Utilisateur créé.'],
        'utilisateur_mis_a_jour' => ['class' => 'alert-success', 'text' => 'Modifications enregistrées.'],
        'utilisateur_supprime' => ['class' => 'alert-danger', 'text' => 'Utilisateur supprimé.'],
    ];
@endphp
@if (session('status') && isset($flashMessages[session('status')]))
    @php $f = $flashMessages[session('status')]; @endphp
    <div class="alert {{ $f['class'] }} flash-global" role="status">{{ $f['text'] }}</div>
@endif
