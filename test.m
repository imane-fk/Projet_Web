function [x1, x2] = degre2(a, b, c)
    delta = b^2 - 4*a*c;  % Discriminant
    if delta < 0
        disp('Pas de solution ...');  % Aucun zéro réel
    else
        x1 = (-b - sqrt(delta)) / (2*a);  % Première solution
        x2 = (-b + sqrt(delta)) / (2*a);  % Deuxième solution
    end
    return
end
