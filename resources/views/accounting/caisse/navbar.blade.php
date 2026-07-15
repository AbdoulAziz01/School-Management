@include('partials.portal-top-navbar', [
    'portalScope' => 'caisse',
    'portalProfileRoute' => route('caisse.profile.edit'),
    'portalRoleLabel' => 'Caissier',
    'portalShowMenuToggle' => true,
])
