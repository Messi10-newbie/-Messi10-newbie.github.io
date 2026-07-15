public class Combo extends ConcessionItem {

    public Combo(String comboType, int quantity) {
        super(comboType, getComboPrice(comboType), quantity);
    }

    private static double getComboPrice(String type) {
        switch (type) {
            case "Standard Combo": return 18.0;
            case "Couple Combo":   return 32.0;
            case "Family Combo":   return 55.0;
            default:               return 18.0;
        }
    }

    @Override
    public String getCategory() { return "Combo"; }

    @Override
    public int hashCode() { return super.hashCode(); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof Combo)) return false;
        return super.equals(obj);
    }
}
