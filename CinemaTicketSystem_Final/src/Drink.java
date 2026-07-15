public class Drink extends ConcessionItem {

    public Drink(String drinkType, int quantity) {
        super(drinkType, getDrinkPrice(drinkType), quantity);
    }

    private static double getDrinkPrice(String type) {
        switch (type) {
            case "Water": return 3.0;
            case "Coke":  return 6.0;
            case "Pepsi": return 6.0;
            case "Juice": return 7.0;
            default:      return 5.0;
        }
    }

    @Override
    public String getCategory() { return "Drink"; }

    @Override
    public int hashCode() { return super.hashCode(); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof Drink)) return false;
        return super.equals(obj);
    }
}
