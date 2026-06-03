@include('partials.portal-top-navbar', [
    'portalScope' => 'admin',
    'portalProfileRoute' => route('admin.profile.edit'),
    'portalRoleLabel' => Auth::user()->isSurveillant() ? 'Surveillant' : 'Admin',
])
