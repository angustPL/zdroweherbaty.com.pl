param(
    [Parameter(Mandatory = $false)]
    [string]$sshUser = "kaate",
    [Parameter(Mandatory = $false)]
    [string]$sshHost = "kaate.pl",
    [Parameter(Mandatory = $false)]
    [string]$dbHost = "178.183.13.109",
    [Parameter(Mandatory = $false)]
    [string]$dbPort = "1433",
    [Parameter(Mandatory = $false)]
    [string]$localPort = "11500"
)

Write-Host "Uruchamiam tunel SSH: $localPort -> $dbHost`:$dbPort przez $sshUser@$sshHost" -ForegroundColor Green
ssh -L ${localPort}:${dbHost}:${dbPort} ${sshUser}@${sshHost} -N
